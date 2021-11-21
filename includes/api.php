<?php
/**
 * Add rest api endpoint for course-booking-system-extension
 */

/**
 * Class Category_List_Rest
 */
class Course_Booking_System_Extension extends WP_REST_Controller
{
    /**
     * The namespace.
     *
     * @var string
     */
    protected $namespace;
    /**
     * Rest base for the current object.
     *
     * @var string
     */
    protected $rest_base;

    /**
     * Category_List_Rest constructor.
     */
    public function __construct()
    {

        $this->namespace = 'wp/v2';
        $this->rest_base = 'course-booking-system-extension';
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/event/(?P<id>\d+)/courses', array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_courses_from_event'), 'args' => array('id' => array('validate_callback' => function ($param, $request, $key)
        {
            return is_numeric($param);
        }, 'required' => true),), 'permission_callback' => array($this, 'api_permission')));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/course/(?P<id>\d+)', array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_course_basic'), 'args' => array('id' => array('validate_callback' => function ($param, $request, $key)
        {
            return is_numeric($param);
        }, 'required' => true),), 'permission_callback' => array($this, 'api_permission')));

        register_rest_route($this->namespace, '/' . $this->rest_base . '/course/(?P<id>\d+)/date/(?P<date>[^/]+)', array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'get_course_date_participants'), 'args' => array('id' => array('validate_callback' => function ($param, $request, $key)
        {
            return is_numeric($param);
        }, 'required' => true), 'date' => array('validate_callback' => function ($param, $request, $key)
        {
            return $this->cbse_is_date($param);
        }, 'required' => true),), 'permission_callback' => array($this, 'api_permission')));
    }

    public function cbse_is_date($date)
    {
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function get_courses_from_event($data)
    {
        global $wpdb;
        $courses = $wpdb->get_results("SELECT `id`,`column_id`, `event_start`, `event_end`, `user_id`, `description` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `event_id` = " . $data['id'] . ";");

        if (empty($courses))
        {
            return new WP_Error('no_event', 'Invalid event', array('status' => 404));
        }

        return new WP_REST_Response($courses);
    }

    public function get_course_basic($data)
    {
        global $wpdb;
        $course_basic = (object)$wpdb->get_row($wpdb->prepare("SELECT `id`,`column_id`, `event_start`, `event_end`, `user_id`, `description`, `event_id` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `id` = %d;", $data['id']));
        $date_booking = $wpdb->get_results($wpdb->prepare("SELECT `date` FROM `" . $wpdb->prefix . "mp_timetable_bookings` WHERE `course_id` =  %d GROUP BY `date`;", $data['id']));
        $date_waitlists = $wpdb->get_results($wpdb->prepare("SELECT `date` FROM `" . $wpdb->prefix . "mp_timetable_waitlists` WHERE `course_id` =  %d GROUP BY `date`;", $data['id']));
        $date_attendances = $wpdb->get_results($wpdb->prepare("SELECT `date` FROM `" . $wpdb->prefix . "mp_timetable_attendances` WHERE `course_id` =  %d GROUP BY `date`;", $data['id']));


        $course_basic->dates = array_unique(array_column(array_merge($date_attendances, $date_booking, $date_waitlists), 'date'));

        if (empty($course_basic->id))
        {
            return new WP_Error('no_course', 'Invalid course', array('status' => 404));
        }


        return new WP_REST_Response($course_basic);
    }

    public function get_course_date_participants($data)
    {
        global $wpdb;
        $course_basic = (object)$wpdb->get_row($wpdb->prepare("SELECT `id`,`column_id`, `event_start`, `event_end`, `user_id`, `description`, `event_id` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `id` = %d;", $data['id']));
        $course_basic->date = $data['date'];
        $course_basic->booking = $wpdb->get_results($wpdb->prepare("SELECT `booking_id`, `user_id` FROM `" . $wpdb->prefix . "mp_timetable_bookings` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $course_basic->waitlists = $wpdb->get_results($wpdb->prepare("SELECT `waitlist_id`, `user_id` FROM `" . $wpdb->prefix . "mp_timetable_waitlists` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $course_basic->attendances = $wpdb->get_results($wpdb->prepare("SELECT `attendance_id`, `attendance` as `user_id` FROM `" . $wpdb->prefix . "mp_timetable_attendances` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $substitutes = $wpdb->get_row($wpdb->prepare("SELECT `user_id` FROM `" . $wpdb->prefix . "mp_timetable_substitutes` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $course_basic->substitutes = $substitutes->user_id ?? null;
        $course_basic->notes = $wpdb->get_results($wpdb->prepare("SELECT `note_id`, `note` FROM `" . $wpdb->prefix . "mp_timetable_notes` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));

        if (empty($course_basic->id))
        {
            return new WP_Error('no_course', 'Invalid course', array('status' => 404));
        }


        return new WP_REST_Response($course_basic);
    }

    public function api_permission($request)
    {
        if (is_user_logged_in())
        {
            if (current_user_can('manage_options'))
            {
                return true;
            }
            else
            {
                return new WP_Error('cbse_not-administrator', __("You are not allowed to read courses.", 'cbse'), array('status' => 403));
            }
        }
        else
        {
            return new WP_Error('cbse_not-logged-in', __("You are not logged in.", 'cbse'), array('status' => 401));
        }
    }
}

/**
 * Function to register our new routes from the controller.
 */
add_action('rest_api_init', function ()
{

    $controller = new Course_Booking_System_Extension();
    $controller->register_routes();

});
