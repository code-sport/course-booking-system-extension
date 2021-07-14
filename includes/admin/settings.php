<?php

function cbse_add_action_links($actions)
{
    $cbseLinks = array(
        '<a href="' . admin_url('options-general.php?page=course_booking_system_extension') . '">' . __('Settings') . '</a>',
    );
    return array_merge($cbseLinks, $actions);
}


function cbse_add_settings_page()
{
    add_options_page('Course Booking System Extension', 'Course Booking System Extension', 'manage_options', 'course_booking_system_extension', 'cbse_render_settings_page');
}

add_action('admin_menu', 'cbse_add_settings_page');

function cbse_register_settings()
{
    if (false === get_option('cbse_options')) {
        cbse_initialize_setting();
    } else {
        cbse_missing_setting();
    }

    //section name, display name, callback to print description of section, page to which section is attached.
    add_settings_section('cbse_header', 'Header', 'cbse_plugin_section_header_text', 'course_booking_system_extension');

    //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
    //last field section is optional.
    add_settings_field('header_image_attachment_id', 'Image Attachment ID', 'cbse_header_image_attachment_id', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('header_title', 'Title', 'cbse_header_title', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('mail_coach_message', 'Coach Mail Message', 'cbse_mail_coach_message', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('mail_categories_title', 'Title for Categories', 'cbse_mail_categories_title', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('mail_categories_exclude', 'Exclude Categories', 'cbse_mail_categories_exclude', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('mail_tags_title', 'Title for Tags', 'cbse_mail_tags_title', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('mail_tags_exclude', 'Exclude Tags', 'cbse_mail_tags_exclude', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('cron_enable', 'Cron Enable', 'cbse_cron_enable', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('cron_before_time', 'Cron Sent before course', 'cbse_cron_before_time', 'course_booking_system_extension', 'cbse_header');

    //section name, form element name, callback for sanitization
    register_setting('cbse_header', 'cbse_options', 'cbse_header_validate');
}

add_action('admin_init', 'cbse_register_settings');

function cbse_initialize_setting()
{
    $settings = [
        'header_image_attachment_id' => '',
        'header_title' => __('Sports operation documentation'),
        'mail_coach_message' => __("Hi %first_name%,\n\nplease note the file in the attachment.\n\nRegards\nYour IT."),
        'mail_categories_title' => __('Categories'),
        'mail_categories_exclude' => '',
        'mail_tags_title' => __('Tags'),
        'mail_tags_exclude' => '',
        'cron_enable' => 'true',
        'cron_before_time_hour' => 2,
        'cron_before_time_minute' => 0
    ];
    add_option('cbse_options', $settings);
}

function cbse_missing_setting()
{
    $options = get_option('cbse_options');

    if (!array_key_exists('header_title', $options)) {
        $options['header_title'] = __('Sports operation documentation');
    }

    if (!array_key_exists('mail_coach_message', $options)) {
        $options['mail_coach_message'] = __("Hi %first_name%,\n\nplease note the file in the attachment.\n\nRegards\nYour IT.");
    }

    if (!array_key_exists('mail_categories_title', $options)) {
        $options['mail_categories_title'] = __('Categories');
    }

    if (!array_key_exists('mail_tags_title', $options)) {
        $options['mail_tags_title'] = __('Tags');
    }

    if (!array_key_exists('cron_enable', $options)) {
        $options['cron_enable'] = 1;
    }

    if (!array_key_exists('cron_before_time_hour', $options)) {
        $options['cron_before_time_hour'] = 2;
    }

    if (!array_key_exists('cron_before_time_minute', $options)) {
        $options['cron_before_time_minute'] = 0;
    }

    update_option('cbse_options', $options);
}

function cbse_render_settings_page()
{
    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <?php settings_errors(); ?>
        <h2><?php _e('Course Booking System Extension'); ?></h2>
        <form action="options.php" method="post">
            <?php
            //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
            settings_fields('cbse_header');
            //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
            do_settings_sections('course_booking_system_extension');
            // Add the submit button to serialize the options
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function cbse_plugin_section_header_text()
{
    echo '<p>' . _e('Here you can set all the options for using the Course Booking System Extension') . '</p>';
}

/* Header Image */
function cbse_header_image_attachment_id()
{
    $options = get_option('cbse_options');
    echo "<input id='header_image_attachment_id' name='cbse_options[header_image_attachment_id]' type='text' value='" . esc_attr($options['header_image_attachment_id'] ?? "") . "' />";
}

/* Header Title */
function cbse_header_title()
{
    $options = get_option('cbse_options');
    echo "<input id='header_title' name='cbse_options[header_title]' type='text' value='" . esc_attr($options['header_title'] ?? "") . "' />";
}

/* Message to coach */
function cbse_mail_coach_message()
{
    $options = get_option('cbse_options');
    echo "<textarea  id='mail_coach_message' name='cbse_options[mail_coach_message]' type='text' row='6' cols='50'>" . esc_attr($options['mail_coach_message'] ?? "") . "</textarea>";
    echo "<p class='description'>" . __('%first_name% will be replaced with the first name of the coach.') . "</p>";
}

/* Categoeries */
function cbse_mail_categories_title()
{
    $options = get_option('cbse_options');
    echo "<input id='mail_categories_title' name='cbse_options[mail_categories_title]' type='text' value='" . esc_attr($options['mail_categories_title'] ?? "") . "' />";
}

function cbse_mail_categories_exclude()
{
    $options = get_option('cbse_options');
    echo "<input id='mail_categories_exclude' name='cbse_options[mail_categories_exclude]' type='text' value='" . esc_attr($options['mail_categories_exclude'] ?? "") . "' />";
    echo "<p class='description'>" . __('0 will hide this field. Please add the values comma seperated.') . "</p>";
}

/* Categoeries */
function cbse_mail_tags_title()
{
    $options = get_option('cbse_options');
    echo "<input id='mail_tags_title' name='cbse_options[mail_tags_title]' type='text' value='" . esc_attr($options['mail_tags_title'] ?? "") . "' />";
}

function cbse_mail_tags_exclude()
{
    $options = get_option('cbse_options');
    echo "<input id='mail_tags_exclude' name='cbse_options[mail_tags_exclude]' type='text' value='" . esc_attr($options['mail_tags_exclude'] ?? "") . "' />";
    echo "<p class='description'>" . __('0 will hide this field. Please add the values comma seperated.') . "</p>";
}

function cbse_cron_enable()
{
    $options = get_option('cbse_options');
    $html = '<input type="checkbox" id="cron_enable" name="cbse_options[cron_enable]" value="1"' . checked(1, $options['cron_enable'], false) . ' disabled="disabled"/>';
    $html .= '<label for="cron_enable">' . __('Sends the head of course a mail with the participants.') . '</label>';

    echo $html;
}

function cbse_cron_before_time()
{
    $options = get_option('cbse_options');
    echo "<input id='cron_before_time_hour' name='cbse_options[cron_before_time_hour]' type='number' min='0' max='23' value='" . esc_attr($options['cron_before_time_hour'] ?? "") . "' />" . __('Hour');
    echo "<input id='cron_before_time_minute' name='cbse_options[cron_before_time_minute]' type='number' min='0' max='59' value='" . esc_attr($options['cron_before_time_minute'] ?? "") . "' />" . __('Minute');
}

/**
 * Validate the input for the header data
 * @param $input
 * @return array
 */
function cbse_header_validate($input)
{
    // Header Image
    $newinput['header_image_attachment_id'] = trim($input['header_image_attachment_id']);
    if (!is_numeric($newinput['header_image_attachment_id'])) {
        $newinput['header_image_attachment_id'] = '';
    }

    $newinput['header_title'] = trim($input['header_title']);
    $newinput['mail_coach_message'] = trim($input['mail_coach_message']);
    $newinput['mail_categories_title'] = trim($input['mail_categories_title']);
    $newinput['mail_categories_exclude'] = trim($input['mail_categories_exclude']);
    $newinput['mail_tags_title'] = trim($input['mail_tags_title']);
    $newinput['mail_tags_exclude'] = trim($input['mail_tags_exclude']);
    $newinput['cron_enable'] = is_numeric($input['cron_enable']) ? $input['cron_enable'] : 1;
    $newinput['cron_before_time_hour'] = is_numeric(trim($input['cron_before_time_hour'])) ? trim($input['cron_before_time_hour']) : 2;
    $newinput['cron_before_time_minute'] = is_numeric(trim($input['cron_before_time_minute'])) ? trim($input['cron_before_time_minute']) : 0;

    cbse_switch_cron(boolval($newinput['cron_enable']));

    return $newinput;
}

function cbse_switch_cron(bool $cronEnabled)
{
    $hook = 'cbse_cron_quarterly_hook';

    if ($cronEnabled) {
        if (!wp_next_scheduled($hook)) {
            wp_schedule_event(time(), 'quarterly', $hook);
        }
    } else {
        $timestamp = wp_next_scheduled($hook);
        wp_unschedule_event($timestamp, $hook);
    }
}

