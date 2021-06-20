<?php

function cbse_add_settings_page()
{
    add_options_page('Course Booking System Extension', 'Course Booking System Extension', 'manage_options', 'course_booking_system_extension', 'cbse_render_settings_page');
}
add_action('admin_menu', 'cbse_add_settings_page');

function cbse_register_settings()
{
    register_setting('cbse_options', 'cbse_options', 'cbse_header_image_attachment_id_validate');
    add_settings_section('cbse_header', 'Header', 'cbse_plugin_section_header_text', 'course_booking_system_extension');

    add_settings_field('cbse_header_image_attachment_id', 'Image Attachment ID', 'cbse_header_image_attachment_id', 'course_booking_system_extension', 'cbse_header');
}

add_action('admin_init', 'cbse_register_settings');

function cbse_render_settings_page()
{
    ?>
    <h2><?php _e('Course Booking System Extension'); ?></h2>
    <form action="options.php" method="post">
        <?php
        settings_fields('cbse_options');
        do_settings_sections('course_booking_system_extension'); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>"/>
    </form>
    <?php
}

function cbse_header_image_attachment_id_validate($input)
{
    $newinput['header_image_attachment_id'] = trim($input['header_image_attachment_id']);
    if (! is_numeric($newinput['header_image_attachment_id'])) {
        $newinput['header_image_attachment_id'] = '';
    }

    return $newinput;
}

function cbse_plugin_section_header_text()
{
    echo '<p>' . _e('Here you can set all the options for using the Course Booking System Extension') . '</p>';
}

function cbse_header_image_attachment_id()
{
    $options = get_option('cbse_options');
    echo "<input id='cbse_header_image_attachment_id' name='cbse_options[header_image_attachment_id]' type='text' value='" . esc_attr($options['header_image_attachment_id']) . "' />";
}

