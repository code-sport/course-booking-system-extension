<?php

namespace CBSE\Admin\User\Shortcode;

use CBSE\Admin\User\UserCovid19Status;
use Exception;

final class ShortcodeUserCovid19Status
{
    protected static ?ShortcodeUserCovid19Status $instance = null;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Init
     */
    private function init()
    {
        add_shortcode('cbse_user_covid19_status', array($this, "showShortcode"));
    }

    /**
     * Return instance
     *
     * @return ShortcodeUserCovid19Status
     */
    public static function getInstance(): ShortcodeUserCovid19Status
    {
        if (null === ShortcodeUserCovid19Status::$instance)
        {
            ShortcodeUserCovid19Status::$instance = new ShortcodeUserCovid19Status();
        }

        return ShortcodeUserCovid19Status::$instance;
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Show shortcode [cbse_user_covid19_status]
     *
     * @param array  $atts    Shortcode attributes. Default empty.
     * @param string $content Shortcode content. Default null.
     * @param string $tag     Shortcode tag (name). Default empty.
     *
     * @return string Shortcode output.
     */
    public function showShortcode($atts = [], $content = null, $tag = '')
    {

        // start box
        $o = '<div class="cbse-box">';

        if (is_user_logged_in())
        {
            $userCovid19Status = new UserCovid19Status(get_current_user_id());
            $covid19Status = $userCovid19Status->getStatus();
            $covid19StatusDate = $userCovid19Status->getDateFormatted();

            if (empty($covid19Status))
            {
                $covid19Status = 'unknown';
            }

            $o .= '<p>';
            switch ($covid19Status)
            {
                case 'tested':
                case 'unknown':
                    $massage = __('Your deposited Covid-19-Status is %s.', CBSE_LANGUAGE_DOMAIN);
                    $o .= wp_sprintf($massage, $covid19Status);
                    break;
                default:
                    $massage = __('Your deposited Covid-19-Status is %s from %s.', CBSE_LANGUAGE_DOMAIN);
                    $o .= wp_sprintf($massage, $covid19Status, $covid19StatusDate);
            }

            $o .= '</p>';

            if (!$userCovid19Status->isValid())
            {
                $o .= '<p style="color: red;">';
                $o .= __('Your status is invalid. Please check it and renew it.', CBSE_LANGUAGE_DOMAIN);
                $o .= '</p>';
            }
        }

        // enclosing tags
        if (!is_null($content))
        {

            $o .= '<p>';
            // secure output by executing the_content filter hook on $content
            // run shortcode parser recursively
            $o .= apply_filters('the_content', do_shortcode($content));
            $o .= '</p>';
        }

        // end box
        $o .= '</div>';

        // return output
        return $o;

    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}

