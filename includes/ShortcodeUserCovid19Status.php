<?php

namespace CBSE;

class ShortcodeUserCovid19Status
{
    protected static $instance;

    /**
     * Shortcode constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Return instance
     *
     * @return Shortcode
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
     * Init
     */
    private function init()
    {
        add_shortcode('cbse_user_covid19_status', array($this, "showShortcode"));
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
            $userId = get_current_user_id();
            $covid19Status = esc_attr(get_the_author_meta('covid-19-status', $userId));
            $covid19StatusDate = esc_attr(get_the_author_meta('covid-19-status_date', $userId));
            $dateString = date(get_option('date_format'), strtotime($covid19StatusDate));

            if (empty($covid19Status))
            {
                $covid19Status = 'unknown';
            }

            $o .= '<p>';
            switch ($covid19Status)
            {
                case 'tested':
                case 'unknown':
                    $massage = __('Your deposited Covid-19-Status is %s.', 'course_booking_system_extension');
                    $o .= wp_sprintf($massage, $covid19Status);
                    break;
                default:
                    $massage = __('Your deposited Covid-19-Status is %s from %s.', 'course_booking_system_extension');
                    $o .= wp_sprintf($massage, $covid19Status, $dateString);
            }

            $o .= '</p>';
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
}

ShortcodeUserCovid19Status::getInstance();
