<?php
/**
 * Add rest api endpoint for course-booking-system-extension
 */

namespace CBSE\Api;

use CBSE\Exception\UnserializeSingletonException;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class Category_List_Rest
 */
final class Api extends WP_REST_Controller
{
    private static ?Api $instance = null;

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
    protected $restBase;

    /**
     * Category_List_Rest constructor.
     *
     *  is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Api::getInstance() instead
     */
    public function __construct()
    {
        $this->namespace = 'wp/v2';
        $this->restBase = 'course-booking-system-extension';

        add_action('rest_api_init', function ()
        {
            $this->registerRoutes();
        });
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function registerRoutes()
    {
        register_rest_route($this->namespace, '/' . $this->restBase . '/event/(?P<id>\d+)/courses', array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'getCoursesFromEvent'), 'args' => array('id' => array('validate_callback' => function ($param, $request, $key)
        {
            return is_numeric($param);
        }, 'required' => true),), 'permission_callback' => array($this, 'apiPermission')));

        register_rest_route($this->namespace, '/' . $this->restBase . '/course/(?P<id>\d+)', array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'getCourseBasic'), 'args' => array('id' => array('validate_callback' => function ($param, $request, $key)
        {
            return is_numeric($param);
        }, 'required' => true),), 'permission_callback' => array($this, 'apiPermission')));

        register_rest_route($this->namespace, '/' . $this->restBase . '/course/(?P<id>\d+)/date/(?P<date>[^/]+)', array('methods' => WP_REST_Server::READABLE, 'callback' => array($this, 'getCourseDateParticipants'), 'args' => array('id' => array('validate_callback' => function ($param, $request, $key)
        {
            return is_numeric($param);
        }, 'required' => true), 'date' => array('validate_callback' => function ($param, $request, $key)
        {
            return $this->isDate($param);
        }, 'required' => true),), 'permission_callback' => array($this, 'apiPermission')));
    }

    public function isDate($date): bool
    {
        return preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date);
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): Api
    {
        if (Api::$instance === null)
        {
            Api::$instance = new Api();
        }

        return Api::$instance;
    }

    public function getCoursesFromEvent($data)
    {
        global $wpdb;
        $courses = $wpdb->get_results("SELECT `id`,`column_id`, `event_start`, `event_end`, `user_id`, `description` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `event_id` = " . $data['id'] . ";");

        if (empty($courses))
        {
            return new WP_Error('no_event', 'Invalid event', array('status' => 404));
        }

        return new WP_REST_Response($courses);
    }

    public function getCourseBasic($data)
    {
        global $wpdb;
        $courseBasic = (object)$wpdb->get_row($wpdb->prepare("SELECT `id`,`column_id`, `event_start`, `event_end`, `user_id`, `description`, `event_id` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `id` = %d;", $data['id']));
        $dateBooking = $wpdb->get_results($wpdb->prepare("SELECT `date` FROM `" . $wpdb->prefix . "mp_timetable_bookings` WHERE `course_id` =  %d GROUP BY `date`;", $data['id']));
        $dateWaitlists = $wpdb->get_results($wpdb->prepare("SELECT `date` FROM `" . $wpdb->prefix . "mp_timetable_waitlists` WHERE `course_id` =  %d GROUP BY `date`;", $data['id']));
        $dateAttendances = $wpdb->get_results($wpdb->prepare("SELECT `date` FROM `" . $wpdb->prefix . "mp_timetable_attendances` WHERE `course_id` =  %d GROUP BY `date`;", $data['id']));


        $courseBasic->dates = array_unique(array_column(array_merge($dateAttendances, $dateBooking, $dateWaitlists), 'date'));

        if (empty($courseBasic->id))
        {
            return new WP_Error('no_course', 'Invalid course', array('status' => 404));
        }


        return new WP_REST_Response($courseBasic);
    }

    public function getCourseDateParticipants($data)
    {
        global $wpdb;
        $courseBasic = (object)$wpdb->get_row($wpdb->prepare("SELECT `id`,`column_id`, `event_start`, `event_end`, `user_id`, `description`, `event_id` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `id` = %d;", $data['id']));
        $courseBasic->date = $data['date'];
        $courseBasic->booking = $wpdb->get_results($wpdb->prepare("SELECT `booking_id`, `user_id` FROM `" . $wpdb->prefix . "mp_timetable_bookings` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $courseBasic->waitlists = $wpdb->get_results($wpdb->prepare("SELECT `waitlist_id`, `user_id` FROM `" . $wpdb->prefix . "mp_timetable_waitlists` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $courseBasic->attendances = $wpdb->get_results($wpdb->prepare("SELECT `attendance_id`, `attendance` as `user_id` FROM `" . $wpdb->prefix . "mp_timetable_attendances` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $substitutes = $wpdb->get_row($wpdb->prepare("SELECT `user_id` FROM `" . $wpdb->prefix . "mp_timetable_substitutes` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));
        $courseBasic->substitutes = $substitutes->user_id ?? null;
        $courseBasic->notes = $wpdb->get_results($wpdb->prepare("SELECT `note_id`, `note` FROM `" . $wpdb->prefix . "mp_timetable_notes` WHERE `course_id` =  %d AND `date` = %s;", $data['id'], $data['date']));

        if (empty($courseBasic->id))
        {
            return new WP_Error('no_course', 'Invalid course', array('status' => 404));
        }


        return new WP_REST_Response($courseBasic);
    }

    public function apiPermission($request)
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

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new UnserializeSingletonException();
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}
