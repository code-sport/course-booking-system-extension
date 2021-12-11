<?php

namespace CBSE;

class UserInformMethod
{
    public function __construct()
    {
        add_action('show_user_profile', [$this, 'showEditUserProfile']);
        add_action('edit_user_profile', [$this, 'showEditUserProfile']);

        add_action('personal_options_update', [$this, 'saveUserProfile']);
        add_action('edit_user_profile_update', [$this, 'saveUserProfile']);
    }

    public function showEditUserProfile($user)
    {
        ?>

        <h3><?php
            _e("Course Booking System Extension Settings", CBSE_LANGUAGE_DOMAIN); ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cbse_inform_method">
                        <?php
                        _e("Auto inform via", CBSE_LANGUAGE_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <?php
                    //get dropdown saved value
                    $selected = get_the_author_meta('cbse-auto-inform', $user->ID);
                    ?>
                    <select name="cbse-auto-inform" id="cbse_inform_method">
                        <option value="none" <?= ($selected == "none") ? 'selected="selected"' : '' ?>>
                            <?php
                            _e('none', CBSE_LANGUAGE_DOMAIN) ?>
                        </option>
                        <option value="email"
                            <?= (empty($selected) || $selected == "email") ? 'selected="selected"' : '' ?>>
                            <?php
                            _e('email', CBSE_LANGUAGE_DOMAIN) ?>
                        </option>
                    </select><br/>
                    <span
                            class="description">
                        <?php
                        _e("Please select how you want to be informed.", CBSE_LANGUAGE_DOMAIN); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cbse_auto_print">
                        <?php
                        _e("Autoprint", CBSE_LANGUAGE_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <?php
                    //get dropdown saved value
                    $selected = get_the_author_meta('cbse-auto-print', $user->ID);
                    ?>
                    <input
                            type="checkbox"
                            id="cbse_auto_print"
                            name="cbse-auto-print"
                            value="1"
                        <?= ($selected == "1") ? 'checked="checked"' : '' ?>
                    >
                    <br/>
                    <span
                            class="description">
                        <?php
                        _e("If activated, the documentation Sport operation in the hall will be printed automatically, as far as a printer is set up.", CBSE_LANGUAGE_DOMAIN);
                        ?>
                    </span>
                </td>
            </tr>
        </table>

        <?php

    }

    public function saveUserProfile($userId)
    {
        if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $userId))
        {
            return false;
        }

        if (!current_user_can('edit_user', $userId))
        {
            return false;
        }
        update_user_meta($userId, 'cbse-auto-inform', $_POST['cbse-auto-inform']);
    }


}

