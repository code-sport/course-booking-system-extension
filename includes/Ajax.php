<?php

namespace CBSE;

require_once 'DocumentationMail.php';

class Ajax
{
    private string $action = 'cbse_event_head_courses';

    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'eventHeadCoursesEnqueue']);
        add_action('wp_ajax_cbse_participants_via_mail', [$this, 'participantsViaMail']);
        add_action('wp_ajax_nopriv_cbse_participants_via_mail', [$this, 'participantsViaMail']);
    }

    function eventHeadCoursesEnqueue($hook)
    {
        global $post;
        if (!(shortcode_exists('cbse_event_head_courses') && has_shortcode($post->post_content, 'cbse_event_head_courses')))
        {
            return;
        }
        wp_enqueue_script('ajax-script', plugins_url('../assets/js/cbse_event_head_courses.js', __FILE__), array('jquery'), get_plugin_data(__FILE__)['Version'], true);
        $title_nonce = wp_create_nonce($this->action);
        wp_localize_script('ajax-script', 'ajax_object', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => $title_nonce, 'hook' => var_export($hook, true),]);
    }

    function participantsViaMail()
    {
        check_ajax_referer($this->action);
        $courseId = intval(sanitize_key($_POST['course_id']));
        $date = sanitize_key($_POST['date']);
        $documentationMail = new DocumentationMail($courseId, $date, get_current_user_id());
        $sent = $documentationMail->sent();
        $args = array('course_id' => $courseId, 'date' => $date, 'sent' => $sent, 'sent_message' => __('Please check your mails'));
        wp_send_json($args);
        wp_die(); // all ajax handlers should die when finished
    }
}


$ajax = new Ajax();


