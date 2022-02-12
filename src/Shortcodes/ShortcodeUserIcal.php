<?php

namespace CBSE\Shortcodes;


use CBSE\Admin\User\UserApiToken;
use Exception;

class ShortcodeUserIcal
{
    protected static ?ShortcodeUserIcal $instance = null;

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
        add_shortcode('cbse_user_ical', array($this, 'showShortcode'));
        add_action('wp_enqueue_scripts', array($this, 'addScripts'));
    }

    /**
     * Return instance
     *
     * @return ShortcodeUserIcal
     */
    public static function getInstance(): ShortcodeUserIcal
    {
        if (null === ShortcodeUserIcal::$instance)
        {
            ShortcodeUserIcal::$instance = new ShortcodeUserIcal();
        }

        return ShortcodeUserIcal::$instance;
    }

    public function addScripts()
    {
        wp_register_style('cbse_user_ical_style', plugins_url('./assets/css/cbse_user_ical.css', CBSE_PLUGIN_BASE_FILE));
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
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
        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);

        // override default attributes with user attributes
        $cbse_atts = shortcode_atts(array('auto-generate' => false), $atts, $tag);
        do_action('qm/debug', $cbse_atts);

        wp_enqueue_style('cbse_user_ical_style');

        if (isset($_POST['cbseNewToken']))
        {
            do_action('qm/debug', $_POST);
            UserApiToken::generateNewTokenForUser(get_current_user_id());
        }

        // start box
        $o = '<div class="cbse-box">';

        if (is_user_logged_in())
        {

            // title
            $o .= '<h3>' . __('iCalendar - The training sessions directly in your digital calendar', CBSE_LANGUAGE_DOMAIN) . '</h3>';

            $userIcalAddress = $this->getIcalAddess($cbse_atts);
            if (!empty($userIcalAddress))
            {
                $o .= '<div class="ical address">';
                $o .= '<p><input id="cbse_ical_address" type="text" readonly value="' . $userIcalAddress . '"></p>';
                $o .= '<p><a href="' . $userIcalAddress . '">' . __('To the ics', CBSE_LANGUAGE_DOMAIN) . '</a></p>';
                $o .= '</div>';
            }

            $o .= '<div class="ical generate">';
            $permalink = get_permalink();
            $o .= "<form name=\"cbse_token_renew\" id=\"cbse_token_renew\" method=\"post\" action=\"$permalink\" >";
            $o .= '<input type="submit" name="cbseNewToken" value="' . __('Generate new token', CBSE_LANGUAGE_DOMAIN) . '">';
            $o .= '</form>';
            $o .= '</div>';


            // enclosing tags
            if (!is_null($content))
            {

                $o .= '<p>';
                // secure output by executing the_content filter hook on $content
                // run shortcode parser recursively
                $o .= apply_filters('the_content', do_shortcode($content));
                $o .= '</p>';
            }
        }

        // end box
        $o .= '</div>';

        // return output
        return $o;

    }

    private function getIcalAddess($cbse_atts)
    {
        $address = UserApiToken::getIcalAddressForUser(get_current_user_id());
        $autoGenerate = boolval($cbse_atts['auto-generate']);
        do_action('qm/debug', ['address' => $address, 'address empty' => empty($address), 'autoGenerate' => $autoGenerate]);
        if (empty($address) && $autoGenerate)
        {
            UserApiToken::generateNewTokenForUser(get_current_user_id());
            $address = UserApiToken::getIcalAddressForUser(get_current_user_id());
            do_action('qm/debug', ['address' => $address]);
        }
        return $address;
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}
