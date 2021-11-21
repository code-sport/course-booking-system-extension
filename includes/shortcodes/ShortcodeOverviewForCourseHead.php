<?php

namespace CBSE\Shortcode;

use CBSE\Dto\CourseInfoDate;
use DateTime;
use Exception;

final  class ShortcodeOverviewForCourseHead
{
    private static ?ShortcodeOverviewForCourseHead $instance = null;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Init
     */
    private function init()
    {
        add_shortcode('cbse_event_head_courses', array($this, "showShortcode"));

        add_action('wp_enqueue_scripts', array($this, "addScripts"));

    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): ShortcodeOverviewForCourseHead
    {
        if (ShortcodeOverviewForCourseHead::$instance === null)
        {
            ShortcodeOverviewForCourseHead::$instance = new ShortcodeOverviewForCourseHead();
        }

        return ShortcodeOverviewForCourseHead::$instance;
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * The [cbse_event_head_courses] shortcode.
     *
     * My course as event head
     *
     * @param array  $atts    Shortcode attributes. Default empty.
     * @param string $content Shortcode content. Default null.
     * @param string $tag     Shortcode tag (name). Default empty.
     *
     * @return string Shortcode output.
     * @throws Exception
     */
    public function showShortcode($atts = [], $content = null, $tag = ''): string
    {
        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);

        // override default attributes with user attributes
        $cbse_atts = shortcode_atts(array('title' => __('My Events as Coach', 'course-booking-system-extension'), 'pastdays' => 7, 'futuredays' => 7), $atts, $tag);

        wp_enqueue_style('cbse_event_head_courses_style');

        $userId = get_current_user_id();
        $isManager = false;

        if (is_user_logged_in() && (current_user_can('administrator') || current_user_can('shop_manager')))
        {
            $isManager = true;
            if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])  /*&& user_id_exists($_GET['user_id'])*/)
            {
                $userId = (int)$_GET['user_id'];
            }
        }

        do_action('qm/debug', $cbse_atts);

        // start box
        $o = '<div class="cbse-box">';

        if (is_user_logged_in())
        {
            if (!empty($cbse_atts['title']))
            {
                // title
                $o .= '<h2>' . esc_html__($cbse_atts['title'], 'course-booking-system-extension') . '</h2>';
            }


            if ($isManager)
            {
                $o .= '<div class="cbse-manager">';
                $o .= '<label for="cbse_switch_coach">' . __('Switch coach', 'course-booking-system-extension') . ' </label>';
                $o .= '<select name="cbse_switch_coach" id="cbse_switch_coach">';
                foreach ($this->getCoaches() as $coach)
                {
                    $user_info = get_userdata($coach->ID);
                    if (!empty($user_info->display_name))
                    {
                        $display_name = esc_html($user_info->display_name);
                    }
                    else
                    {
                        $display_name = __('Without name', 'course-booking-system');
                    }
                    $o .= '<option value="' . esc_html($coach->ID) . '" ' . (($coach->ID == $userId) ? ' selected="selected"' : '') . '">' . $display_name . '</option>';
                }
                $o .= '</select>';
                $o .= '</div>';
            }

            // Display settings
            $o .= '<div class="cbse-time-settings">';
            $o .= __('show courses of', 'course-booking-system-extension') . '<br />';
            $o .= '<input type="checkbox" id="cbse-time-past" name="cbse-time-past" value="past" checked><label for="cbse-time-past">' . __('past', 'course-booking-system-extension') . '</label><br />';
            $o .= '<input type="checkbox" id="cbse-time-today" name="cbse-time-today" value="today" checked><label for="cbse-time-today">' . __('today', 'course-booking-system-extension') . '</label><br />';
            $o .= '<input type="checkbox" id="cbse-time-future" name="cbse-time-future" value="future" checked><label for="cbse-time-future">' . __('future', 'course-booking-system-extension') . '</label>';
            $o .= '</div>';

            //list with trainings
            $o .= '<div class="cbse-courses">';
            $o .= '<ul class="cbse_timeslots">';
            $timeslots = cbse_courses_for_head($userId, $cbse_atts['pastdays'], $cbse_atts['futuredays']);
            foreach ($timeslots as $timeslot)
            {
                $dataDate = $timeslot->date;

                $args = array();
                $args['timeslot'] = $timeslot;
                $args['courseInfo'] = new CourseInfoDate($timeslot->course_id, DateTime::createFromFormat('Y-m-d', $dataDate));

                $dataStartTime = $timeslot->date . ' ' . $args['timeslot']->event_start;
                $dataStartDateTime = strtotime($dataStartTime);
                $courseId = $timeslot->course_id;

                $o .= "<li class='cbse_timeslot' data-startdate='$dataDate' data-starttime='$dataStartTime' data-startdatetime='$dataStartDateTime' date-courseid='$courseId'>";
                ob_start();
                if (get_template_part('mp-timetable/shortcodes/cbse_event_head_courses', 'single', $args) === false)
                {
                    $o .= '<p>Error: Cloud not load template part</p>';
                }
                $o .= ob_get_clean();
                $o .= '</li>';
            }
            $o .= '</ul>';
            $o .= '</div>';

        }
        else
        {
            $loginArgs = array('echo' => false, 'redirect' => get_permalink(get_the_ID()), 'remember' => true,);
            $o .= wp_login_form($loginArgs);
        }

        // enclosing tags
        if (!is_null($content))
        {
            // secure output by executing the_content filter hook on $content
            $o .= apply_filters('the_content', $content);

            // run shortcode parser recursively
            $o .= do_shortcode($content);
        }

        // end box
        $o .= '</div>';

        // return output
        return $o;
    }

    private function getCoaches(): array
    {
        return get_users(['role__in' => ['administrator', 'editor', 'author', 'contributor']]);
    }

    public function addScripts()
    {
        wp_register_style('cbse_event_head_courses_style', plugins_url('../assets/css/cbse_event_head_courses.css', __FILE__));
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}

ShortcodeOverviewForCourseHead::getInstance();
