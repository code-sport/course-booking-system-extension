<?php

namespace CBSE;

class UserCovid19Status
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
        $disabled = '';
        if (!$this->isManager())
        {
            $disabled = 'disabled';
        }
        ?>

        <h3><?php
            _e("Covid-19 Status", 'course_booking_system_extension'); ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row"><label
                            for="covid-19-status"><?php
                        _e("Status", 'course_booking_system_extension'); ?></label></th>
                <td>
                    <?php
                    //get dropdown saved value
                    $selected = get_the_author_meta('covid-19-status', $user->ID);
                    $selectedHtml = 'selected="selected"';
                    ?>
                    <select name="covid-19-status" id="covid-19-status" <?= $disabled ?>>
                        <option value="tested"
                            <?php
                            echo ($selected == "tested") ? $selectedHtml : '' ?>>
                            <?php
                            _e('tested', 'course_booking_system_extension') ?>
                        </option>
                        <option value="vaccinated"
                            <?php
                            echo ($selected == "vaccinated") ? $selectedHtml : '' ?>>
                            <?php
                            _e('vaccinated', 'course_booking_system_extension') ?>
                        </option>
                        <option value="recovered"
                            <?php
                            echo ($selected == "recovered") ? $selectedHtml : '' ?>>
                            <?php
                            _e('recovered', 'course_booking_system_extension') ?>
                        </option>
                        <option value="unknown"
                            <?php
                            echo (empty($selected) || $selected == "unknown") ? $selectedHtml : '' ?>>
                            <?php
                            _e('unknown', 'course_booking_system_extension') ?>
                        </option>
                    </select><br/>
                    <span class="description">
                        <?php
                        _e("Please select your covid-19 status.", 'course_booking_system_extension'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="covid-19-status_date">
                        <?php
                        _e("Date", 'course_booking_system_extension'); ?>
                    </label>
                </th>
                <td>
                    <?php
                    // get test saved value
                    $saved = esc_attr(get_the_author_meta('covid-19-status_date', $user->ID));
                    ?>
                    <input type="date" name="covid-19-status_date" id="covid-19-status_date"
                           value="<?php
                           echo $saved; ?>"
                           class="regular-text" placeholder="YYYY-MM-DD"
                        <?= $disabled ?>/><br/>
                    <span
                            class="description">
                        <?php
                        _e('Select the date of your covid-19-status', 'course_booking_system_extension') ?>
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

        if (!current_user_can('edit_user', $userId) && !$this->isManager())
        {
            return false;
        }
        update_user_meta($userId, 'covid-19-status', $_POST['covid-19-status']);
        update_user_meta($userId, 'covid-19-status_date', $_POST['covid-19-status_date']);
    }


    /**
     * Check if the current user is a Manger
     * TODO: Move into static class
     *
     * @return bool
     */
    private function isManager(): bool
    {
        return current_user_can('administrator') || current_user_can('shop_manager');
    }
}

$var = new UserCovid19Status();
