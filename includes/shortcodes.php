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

    // start box
    $o = '<div class="cbse-box">';

    // title
    $o .= '<h2>' . esc_html__($cbse_atts['title'], 'cbse') . '</h2>';

    if (is_user_logged_in()) {
        //list with trainings
        $o .= '<ul>';
        $timeslots = cbse_courses_for_head(get_current_user_id());
        foreach ($timeslots as $timeslot) {
            //var_dump($timeslot);
            $courseInfo = cbse_course_info($timeslot->course_id);
            $bookings = cbse_course_date_bookings($timeslot->course_id, $timeslot->date, $cbse_atts['pastdays'], $cbse_atts['futuredays']);

            //var_dump($courseInfo->event_categories);

            $o .= '<li><p>' . $courseInfo->column->post_title . ', ' . $timeslot->date . ' ' . $courseInfo->event->post_title . ' ' . $timeslot->event_start . ' - ' . $timeslot->event_end . ' #' . $timeslot->course_id . '</p>';
            $o .= '<p>' . __('Bookings');

            $o .= '<ol>';
            foreach ($bookings as $booking) {
                $o .= '<li>' . $booking->last_name . ', ' . $booking->first_name . '</li>';
            }
            $o .= '</ol>';
            $o .= '</p>';
            $o .= '<p>(' . $timeslot->bookings . ' | ' . $timeslot->waitings . ' | ' . $courseInfo->event_meta->attendance . ') <button type="button" class="cbse cbse_participants_via_email" data-button=\'' . json_encode(array("course_id" => $timeslot->course_id, "date" => $timeslot->date)) . '\'>' . __('Participants via email') . '</button></p>';
            $o .= '</li>';
        }
        $o .= '</ul>';
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

add_shortcode('cbse_event_head_courses', 'cbse_event_head_courses_shortcode');
