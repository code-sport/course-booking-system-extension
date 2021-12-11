<?php

// ./src/CBSE.php

namespace CBSE;

use CBSE\Dto\CourseInfoDate;
use DateTime;

class Ajax
{
    private string $action = 'cbse_event_head_courses';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'eventHeadCoursesEnqueue']);
        add_action('wp_ajax_cbse_participants_via_mail', [$this, 'participantsViaMail']);
        add_action('wp_ajax_nopriv_cbse_participants_via_mail', [$this, 'participantsViaMail']);
    }

    public function eventHeadCoursesEnqueue($hook)
    {
        global $post;
        if (
            !(shortcode_exists('cbse_event_head_courses')
                && has_shortcode($post->post_content, 'cbse_event_head_courses'))
        )
        {
            return;
        }
        wp_enqueue_script('ajax-script',
            plugins_url('../assets/js/cbse_event_head_courses.js', __FILE__),
            array('jquery'), get_plugin_data(__FILE__)['Version'],
            true
        );
        $titleNonce = wp_create_nonce($this->action);
        wp_localize_script('ajax-script',
            'ajax_object',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => $titleNonce,
                'hook' => var_export($hook, true),
            ]
        );
    }

    public function participantsViaMail()
    {
        check_ajax_referer($this->action);
        $courseId = intval(sanitize_key($_POST['course_id']));
        $date = DateTime::createFromFormat('Y-m-d', sanitize_key($_POST['date']));
        $course = new CourseInfoDate($courseId, $date);
        $documentationMail = new DocumentationMail($course);
        $sent = $documentationMail->sentToUser(get_current_user_id());
        $args = array('course_id' => $courseId,
            'date' => $date, 'sent' => $sent,
            'sent_message' => __('Please check your mails')
        );
        wp_send_json($args);
        // all ajax handlers should die when finished
        wp_die();
    }
}

