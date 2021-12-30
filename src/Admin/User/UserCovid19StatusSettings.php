<?php

namespace CBSE\Admin\User;

use Analog;

class UserCovid19StatusSettings
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
            _e("Covid-19 Status", CBSE_LANGUAGE_DOMAIN); ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row"><label
                            for="covid-19-status"><?php
                        _e("Status", CBSE_LANGUAGE_DOMAIN); ?></label></th>
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
                            _e('tested', CBSE_LANGUAGE_DOMAIN) ?>
                        </option>
                        <option value="vaccinated"
                            <?php
                            echo ($selected == "vaccinated") ? $selectedHtml : '' ?>>
                            <?php
                            _e('vaccinated', CBSE_LANGUAGE_DOMAIN) ?>
                        </option>
                        <option value="vaccinated_updated"
                            <?php
                            echo ($selected == "vaccinated_updated") ? $selectedHtml : '' ?>>
                            <?php
                            _e('booster vaccinated', CBSE_LANGUAGE_DOMAIN) ?>
                        </option>
                        <option value="recovered"
                            <?php
                            echo ($selected == "recovered") ? $selectedHtml : '' ?>>
                            <?php
                            _e('recovered', CBSE_LANGUAGE_DOMAIN) ?>
                        </option>
                        <option value="unknown"
                            <?php
                            echo (empty($selected) || $selected == "unknown") ? $selectedHtml : '' ?>>
                            <?php
                            _e('unknown', CBSE_LANGUAGE_DOMAIN) ?>
                        </option>
                    </select><br/>
                    <span class="description">
                        <?php
                        _e("Please select your covid-19 status.", CBSE_LANGUAGE_DOMAIN); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="covid-19-status_date">
                        <?php
                        _e("Date", CBSE_LANGUAGE_DOMAIN); ?>
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
                        _e('Select the date of your covid-19-status', CBSE_LANGUAGE_DOMAIN) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="covid-19-status_employee">
                        <?php
                        _e("employee", CBSE_LANGUAGE_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <?php
                    // get test saved value
                    $selected = get_the_author_meta('covid-19-status_employee', $user->ID);
                    ?>
                    <input
                            type="checkbox"
                            id="covid-19-status_employee"
                            name="covid-19-status_employee"
                            value="1"
                        <?= ($selected == "1") ? 'checked="checked"' : '';
                        $disabled ?>
                    />
                    <br/>
                    <span class="description">
                        <?php
                        _e('Is this user an employee from this organization?', CBSE_LANGUAGE_DOMAIN) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="covid-19-status_top-athlete">
                        <?php
                        _e("top athlete", CBSE_LANGUAGE_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <?php
                    // get test saved value
                    $selected = get_the_author_meta('covid-19-status_top-athlete', $user->ID);
                    ?>
                    <input
                            type="checkbox"
                            id="covid-19-status_top-athlete"
                            name="covid-19-status_top-athlete"
                            value="1"
                        <?= ($selected == "1") ? 'checked="checked"' : '';
                        $disabled ?>
                    />
                    <br/>
                    <span class="description">
                        <?php
                        _e('Is this user an top athlete?', CBSE_LANGUAGE_DOMAIN) ?>
                    </span>
                </td>
            </tr>
        </table>

        <?php

    }

    /**
     * Check if the current user is a Manger
     *
     * @return bool
     */
    private function isManager(): bool
    {
        return current_user_can('administrator') || current_user_can('shop_manager');
    }

    public function saveUserProfile($userId): bool
    {
        $postData = $_POST;
        if (empty($postData['_wpnonce']) || !wp_verify_nonce($postData['_wpnonce'], 'update-user_' . $userId))
        {
            return false;
        }

        if (!current_user_can('edit_user', $userId) && !$this->isManager())
        {
            return false;
        }

        $this->updateUserMetaAndLog($userId, 'covid-19-status', $postData['covid-19-status']);
        $this->updateUserMetaAndLog($userId, 'covid-19-status_date', $postData['covid-19-status_date']);
        $this->updateUserMetaAndLog($userId, 'covid-19-status_employee', $postData['covid-19-status_employee']);
        $this->updateUserMetaAndLog($userId, 'covid-19-status_top-athlete', $postData['covid-19-status_top-athlete']);

        return true;
    }

    private function updateUserMetaAndLog(int $user_id, string $meta_key, $meta_value): void
    {
        $previous = get_user_meta($user_id, $meta_key, true);

        if ($previous != $meta_value)
        {
            Analog::info(get_current_user_id() . ' updated ' . $user_id . ' on ' . $meta_key . ' with ' . $meta_value .
                ' it was ' . $previous);
        }

        update_user_meta($user_id, $meta_key, $meta_value, $previous);

    }
}


