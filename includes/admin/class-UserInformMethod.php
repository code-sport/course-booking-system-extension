<?php

namespace CBSE;

class UserInformMethod
{
    public function __construct()
    {
        add_action('show_user_profile', [$this, 'show_edit_user_profile']);
        add_action('edit_user_profile', [$this, 'show_edit_user_profile']);

        add_action('personal_options_update', 'save_user_profile');
        add_action('edit_user_profile_update', 'save_user_profile');
    }

    public function show_edit_user_profile($user)
    {
        ?>

        <h3><?php _e("Course Booking System Extension Settings", 'course_booking_system_extension'); ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row"><label
                        for="cbse_inform_method"><?php _e("Auto inform via", 'course_booking_system_extension'); ?></label>
                </th>
                <td>
                    <?php
                    //get dropdown saved value
                    $selected = get_the_author_meta('cbse-auto-inform', $user->ID);
                    ?>
                    <select name="cbse-auto-inform" id="cbse_inform_method">
                        <option
                            value="none" <?= ($selected == "none") ? 'selected="selected"' : '' ?>><?php _e('none', 'course_booking_system_extension') ?></option>
                        <option
                            value="email" <?= (empty($selected) || $selected == "email") ? 'selected="selected"' : '' ?>><?php _e('email', 'course_booking_system_extension') ?></option>
                    </select><br/>
                    <span
                        class="description"><?php _e("Please select how you want to be informed.", 'course_booking_system_extension'); ?></span>
                </td>
            </tr>
        </table>

        <?php

    }

    public function save_user_profile($user_id)
    {
        if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
            return false;
        }

        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_user_meta($user_id, 'cbse-auto-inform', $_POST['cbse-auto-inform']);
    }


}

$var = new UserInformMethod();
