<?php

function cbse_add_action_links($actions)
{
    $cbseLinks = array(
        '<a href="' . admin_url('options-general.php?page=course_booking_system_extension') . '">' . __('Settings',
            'course_booking_system_extension') . '</a>',
    );
    return array_merge($cbseLinks, $actions);
}
