<?php

namespace CBSE\Admin;

use CBSE\UserCovid19Status;
use Exception;

class UserCovid19StatusOverview
{
    private static ?UserCovid19StatusOverview $instance = null;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        add_filter('manage_users_columns', [$this, 'columnHeader']);
        add_filter('manage_users_custom_column', [$this, 'columnRow'], 10, 3);
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): UserCovid19StatusOverview
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public function columnHeader($column)
    {
        $column['Covid19Status'] = __('Covid-19-Status', CBSE_LANGUAGE_DOMAIN);
        return $column;
    }

    public function columnRow($val, $columnName, $userId)
    {
        switch ($columnName)
        {
            case 'Covid19Status' :
                return $this->getStatus($userId);
            default:
        }
        return $val;
    }

    private function getStatus(int $userId): string
    {
        $statusFromUser = new UserCovid19Status($userId);
        $style = '';

        if(!$statusFromUser->isValid())
        {
            $style .= 'color: red;';
        }

        $content = "<p style='$style'>{$statusFromUser->getStatusOrEmpty()}<br />";
        $content .= "{$statusFromUser->getDateFormatted()}</p>";

        if($statusFromUser->getFlags())
        {
            $content .= "<p>{$statusFromUser->getFlags()}</p>";
        }

        return $content;
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}
