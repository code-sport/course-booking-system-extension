<?php

namespace CBSE;

class Templates
{
    public function init()
    {
        do_action('qm/info', 'init');
        add_action('wp_head', [$this, 'requiredFiles']);
    }

    public function requiredFiles()
    {
        do_action('qm/info', 'cbse_required_files');
        // Shortcodes / Table Header
        $fileTheme = get_template_directory() . '/mp-timetable/shortcodes/cbse_event_head_courses-single.php';
        $filePlugin = plugin_dir_path(__FILE__) . '../templates/shortcodes/cbse_event_head_courses-single.php';

        if (!file_exists($fileTheme))
        {
            if (!file_exists(dirname($fileTheme)))
            {
                // trick to make sure the folder exists first
                mkdir(dirname($fileTheme), 0777, true);
            }
            if (!copy($filePlugin, $fileTheme))
            {
                add_action('admin_notices', [$this, 'requiredFileEventHeadCoursesSingle']);
            }
        }
    }

    public function requiredFileEventHeadCoursesSingle()
    {
        ?>
        <div class="notice">
            <p><?= sprintf(__('The plugin "Course Booking System Extension" could not add the file "%s" to your theme. This file is required to ensure that the plugin works without restrictions. Please add the file to your theme manually or fix the error that the file could not be copied.', CBSE_LANGUAGE_DOMAIN), 'cbse_event_head_courses-single') ?></p>
        </div>
        <?php
    }
}

$templates = new Templates();
$templates->init();
