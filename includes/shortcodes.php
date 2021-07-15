<?php

/**
 * The [cbse_event_head_courses] shortcode.
 *
 * My course as event head
 *
 * @param array $atts Shortcode attributes. Default empty.
 * @param string $content Shortcode content. Default null.
 * @param string $tag Shortcode tag (name). Default empty.
 * @return string Shortcode output.
 */
function cbse_event_head_courses_shortcode($atts = [], $content = null, $tag = '')
{
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    // override default attributes with user attributes
    $cbse_atts = shortcode_atts(
        array(
            'title' => __('My Events as Coach'),
            'pastdays' => 7,
            'futuredays' => 7
        ), $atts, $tag
    );

    wp_enqueue_style('cbse_event_head_courses_style');

    $userId = get_current_user_id();
    $isManager = false;

    if (is_user_logged_in() && (current_user_can('administrator') || current_user_can('shop_manager'))) {
        $isManager = true;
        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])  /*&& user_id_exists($_GET['user_id'])*/) {
            $userId = (int)$_GET['user_id'];
        }
    }

    do_action('qm/debug', $cbse_atts);

    // start box
    $o = '<div class="cbse-box">';

    if (is_user_logged_in()) {
        if (!empty($cbse_atts['title'])) {
            // title
            $o .= '<h2>' . esc_html__($cbse_atts['title'], 'cbse') . '</h2>';
        }


        if ($isManager) {
            $o .= '<div class="cbse-manager">';
            $o .= '<label for="cbse_switch_coach">' . __('Switch coach') . ' </label>';
            $o .= '<select name="cbse_switch_coach" id="cbse_switch_coach">';
            foreach (cbse_get_coaches() as $coach) {
                $user_info = get_userdata($coach->ID);
                if (!empty($user_info->display_name)) {
                    $display_name = esc_html($user_info->display_name);
                } else {
                    $display_name = __('Without name', 'course-booking-system');
                }
                $o .= '<option value="' . esc_html($coach->ID) . '" ' . (($coach->ID == $userId) ? ' selected="selected"' : '') . '">' . $display_name . '</option>';
            }
            $o .= '</select>';
            $o .= '</div>';
        }

        //list with trainings
        $o .= '<div class="cbse-courses">';
        $o .= '<ul class="cbse_timeslots">';
        $timeslots = cbse_courses_for_head($userId, $cbse_atts['pastdays'], $cbse_atts['futuredays']);
        foreach ($timeslots as $timeslot) {
            $args = array();
            $args['timeslot'] = $timeslot;
            $args['courseInfo'] = cbse_course_info($timeslot->course_id, $timeslot->date);
            $args['bookings'] = cbse_course_date_bookings($timeslot->course_id, $timeslot->date);

            $o .= '<li class="cbse_timeslot">';
            ob_start();
            if (get_template_part('mp-timetable/shortcodes/cbse_event_head_courses', 'single', $args) === false) {
                $o .= '<p>Error: Cloud not load template part</p>';
            }
            $o .= ob_get_clean();
            $o .= '</li>';
        }
        $o .= '</ul>';
        $o .= '</div>';

    }

    // enclosing tags
    if (!is_null($content)) {
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

function cbse_event_head_courses_shortcode_styles()
{
    wp_register_style('cbse_event_head_courses_style', plugins_url('../assets/css/cbse_event_head_courses.css', __FILE__));
}

function cbse_get_coaches(): array
{
    return get_users(['role__in' => ['administrator', 'editor', 'author', 'contributor']]);
}

add_shortcode('cbse_event_head_courses', 'cbse_event_head_courses_shortcode');
add_action('wp_enqueue_scripts', 'cbse_event_head_courses_shortcode_styles');
