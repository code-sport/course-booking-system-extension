<?php

class CBSE_Templates
{
    public function init()
    {
        do_action('qm/info', 'init');
        add_action('wp_head', [$this, 'cbse_required_files']);
    }

    function cbse_required_files()
    {
        do_action('qm/info', 'cbse_required_files');
        // Shortcodes / Table Header
        $file_theme = get_template_directory() . '/mp-timetable/shortcodes/cbse_event_head_courses-single.php';
        $file_plugin = plugin_dir_path(__FILE__) . '../templates/shortcodes/cbse_event_head_courses-single.php';

        if (!file_exists($file_theme))
        {
            if (!file_exists(dirname($file_theme)))
            {
                mkdir(dirname($file_theme), 0777, true); // trick to make sure the folder exists first
            }
            if (!copy($file_plugin, $file_theme))
            {
                add_action('admin_notices', [$this, 'cbs_required_file_event_head_courses_single']);
            }
        }
    }

    function cbs_required_file_event_head_courses_single()
    {
        ?>
        <div class="notice">
            <p><?= sprintf(__('The plugin "Course Booking System Extension" could not add the file "%s" to your theme. This file is required to ensure that the plugin works without restrictions. Please add the file to your theme manually or fix the error that the file could not be copied.', 'course-booking-system-extension'), 'cbse_event_head_courses-single') ?></p>
        </div>
        <?php
    }
}

$templates = new CBSE_Templates();
$templates->init();
