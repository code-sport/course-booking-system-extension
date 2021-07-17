<?php

add_action('show_user_profile', 'cbse_extra_user_profile_fields');
add_action('edit_user_profile', 'cbse_extra_user_profile_fields');

function cbse_extra_user_profile_fields($user)
{ ?>
    <h3><?php _e("Covid-19", "blank"); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="covid-19-status"><?php _e("Status"); ?></label></th>
            <td>
                <?php
                //get dropdown saved value
                $selected = get_the_author_meta('covid-19-status', $user->ID);
                ?>
                <select name="covid-19-status" id="covid-19-status">
                    <option
                        value="tested" <?php echo ($selected == "tested") ? 'selected="selected"' : '' ?>><?php _e('tested') ?></option>
                    <option
                        value="vaccinated" <?php echo ($selected == "vaccinated") ? 'selected="selected"' : '' ?>><?php _e('vaccinated') ?></option>
                    <option
                        value="recovered" <?php echo ($selected == "recovered") ? 'selected="selected"' : '' ?>><?php _e('recovered') ?></option>
                    <option
                        value="unknown" <?php echo (empty($selected) || $selected == "unknown") ? 'selected="selected"' : '' ?>><?php _e('unknown') ?></option>
                </select><br/>
                <span class="description"><?php _e("Please select your covid-19 status."); ?></span>
            </td>
        </tr>
        <tr>
            <th><label for="covid-19-status_date"><?php _e("Date"); ?></label></th>
            <td>
                <?php
                // get test saved value
                $saved = esc_attr(get_the_author_meta('covid-19-status_date', $user->ID));
                ?>
                <input type="date" name="covid-19-status_date" id="covid-19-status_date" value="<?php echo $saved; ?>"
                       class="regular-text" placeholder="YYYY-MM-DD"/><br/>
                <span class="description">Select the date of your covid-19-status</span>
            </td>
        </tr>
        <tr>
            <th><label for="covid-19-status"><?php _e("Auto inform via"); ?></label></th>
            <td>
                <?php
                //get dropdown saved value
                $selected = get_the_author_meta('cbse-auto-inform', $user->ID);
                ?>
                <select name="covid-19-status" id="covid-19-status">
                    <option
                        value="none" <?= ($selected == "none") ? 'selected="selected"' : '' ?>><?php _e('none') ?></option>
                    <option
                        value="email" <?= (empty($selected) || $selected == "email") ? 'selected="selected"' : '' ?>><?php _e('email') ?></option>
                </select><br/>
                <span
                    class="description"><?php _e("Please select how you want to be informed."); ?></span>
            </td>
        </tr>
    </table>
<?php }

add_action('personal_options_update', 'cbse_save_extra_user_profile_fields');
add_action('edit_user_profile_update', 'cbse_save_extra_user_profile_fields');

function cbse_save_extra_user_profile_fields($user_id)
{
    if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
        return;
    }

    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    update_user_meta($user_id, 'covid-19-status', $_POST['covid-19-status']);
    update_user_meta($user_id, 'covid-19-status_date', $_POST['covid-19-status_date']);
}
