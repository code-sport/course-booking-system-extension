<?php

add_action('wp_enqueue_scripts', 'cbse_event_head_courses_enqueue');
function cbse_event_head_courses_enqueue($hook)
{
    global $post;
    if (!(shortcode_exists('cbse_event_head_courses') && has_shortcode($post->post_content, 'cbse_event_head_courses'))) {
        return;
    }
    wp_enqueue_script(
        'ajax-script',
        plugins_url('../assets/js/cbse_event_head_courses.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );
    $title_nonce = wp_create_nonce('cbse_event_head_courses');
    wp_localize_script(
        'ajax-script',
        'ajax_object',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $title_nonce,
            'hook' => var_export($hook, true),
        ]
    );
}

add_action('wp_ajax_cbse_participants_via_mail', 'cbse_participants_via_mail');
add_action('wp_ajax_nopriv_cbse_participants_via_mail', 'cbse_participants_via_mail');
function cbse_participants_via_mail()
{
    check_ajax_referer('cbse_event_head_courses');
    $courseId = intval(sanitize_key($_POST['course_id']));
    $date = sanitize_key($_POST['date']);
    $sent = cbse_sent_mail_with_course_date_bookings($courseId, $date, get_current_user_id());
    $args = array(
        'course_id' => $courseId,
        'date' => $date,
        'sent' => $sent,
        'sent_message' => __('Please check your mails')
    );
    wp_send_json($args);
    wp_die(); // all ajax handlers should die when finished
}
