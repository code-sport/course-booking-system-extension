<?php

namespace CBSE\Admin\User;

use Analog\Analog;
use CBSE\Helper\RandomHelper;
use CBSE\Helper\UserHelper;
use Exception;

class UserApiToken
{
    private static ?UserApiToken $instance = null;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        add_action('show_user_profile', [$this, 'showEditUserProfile']);
        add_action('edit_user_profile', [$this, 'showEditUserProfile']);

        add_action('personal_options_update', [$this, 'saveUserProfile']);
        add_action('edit_user_profile_update', [$this, 'saveUserProfile']);
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): UserApiToken
    {
        if (static::$instance === null)
        {
            static::$instance = new UserApiToken();
        }

        return static::$instance;
    }

    public function showEditUserProfile($user)
    {
        $token = get_the_author_meta('cbse-api-token', $user->ID);
        $icalAddress = '';
        if (!empty($token))
        {
            $icalAddress = home_url("/wp-json/wp/v2/course-booking-system-extension/calender/{$user->ID}/{$token}/calender.ics");
        }

        ?>
        <h3><?= _e('Course Bookings System Extension API Token', CBSE_LANGUAGE_DOMAIN) ?></h3>
        <table class="form-table">
            <?php
            if (UserHelper::isUserManager(get_current_user_id())): ?>
                <tr>
                    <th scope="row">
                        <label for="cbse_api_token">
                            <?php
                            _e("Token", CBSE_LANGUAGE_DOMAIN); ?>
                        </label>
                    </th>
                    <td>
                        <input id="cbse_api_token" name="cbse-api-token" value="<?= $token ?>" readonly
                               class="regular-text"/>
                    </td>
                </tr>
            <?php
            endif; ?>
            <tr>
                <th scope="row">
                    <label for="cbse_ics">
                        <?php
                        _e("ics", CBSE_LANGUAGE_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <input id="cbse_ics" name="cbse_ics" value="<?= $icalAddress ?>" readonly class="regular-text"/>
                    <?php
                    if (!empty($token)): ?>
                        <br/>
                        <span class="description"> <a
                                    href="<?= $icalAddress ?>"><?= _e('To the ics', CBSE_LANGUAGE_DOMAIN) ?></a> </span>
                    <?php
                    endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="cbse_renew_token">
                        <?php
                        _e("Renew Token", CBSE_LANGUAGE_DOMAIN); ?>
                    </label>
                </th>
                <td>
                    <input
                            type="checkbox"
                            id="cbse_renew_token"
                            name="cbse-renew-token"
                            value="1"
                    />
                    <br/>
                    <span
                            class="description">
                        <?php
                        _e("If activated, the course booking system extenstion token will be renewed and the old token are not valid any more.", CBSE_LANGUAGE_DOMAIN);
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

        if ($_POST['cbse-renew-token'] == 1)
        {
            $token = $this->generateToken();
        }

        update_user_meta($userId, 'cbse-api-token', $token);
    }

    private function generateToken(): string
    {
        $strength = 60;
        try
        {
            return RandomHelper::secureString($strength);
        } catch (Exception $e)
        {
            Analog::error($e);
            return '';
        }
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}