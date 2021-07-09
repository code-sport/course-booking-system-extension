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
    }

    //section name, display name, callback to print description of section, page to which section is attached.
    add_settings_section('cbse_header', 'Header', 'cbse_plugin_section_header_text', 'course_booking_system_extension');

    //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
    //last field section is optional.
    add_settings_field('header_image_attachment_id', 'Image Attachment ID', 'cbse_header_image_attachment_id', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('header_title', 'Title', 'cbse_header_title', 'course_booking_system_extension', 'cbse_header');
    add_settings_field('mail_coach_message', 'Coach Mail Message', 'cbse_mail_coach_message', 'course_booking_system_extension', 'cbse_header');

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
    ];
    add_option('cbse_options', $settings);
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
    echo "<p class='description'>%first_name% will be replaced with the first name of the coach.</p>";
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

    return $newinput;
}


