<?php

//defined('TEST');

namespace CBSE\Admin\User\Cron;

use DateTime;

abstract class CronBase
{

    private $hook;

    protected function __construct(string $hook)
    {
        $this->hook = $hook;
        add_filter('cron_schedules', [$this, 'addCronQuarterlyInterval']);
        add_action($this->hook, [$this, 'quarterlyExec']);
    }


    public function addCronQuarterlyInterval($schedules)
    {
        $schedules['quarterly'] = array('interval' => 900, 'display' => esc_html__('Every 15 Minutes'),);
        return $schedules;
    }


    public function quarterlyExec()
    {
        $timeNow = time();
        $dateNow = new DateTime();
        $dateNow->setTimestamp($timeNow);
        $dateNow->setTimezone(wp_timezone());

        $lastRun = $this->getLastRun();
        $dateLastRun = new DateTime();
        $dateLastRun->setTimestamp($lastRun);
        $dateLastRun->setTimezone(wp_timezone());

        // file_put_contents('cron.txt', $dateLastRun->format('c') . PHP_EOL, FILE_APPEND);

        if ($lastRun === false)
        {
            add_option($this->getOptionNameLastRun(), $timeNow);
        }
        else
        {
            $this->work($dateLastRun, $dateNow);
            update_option($this->getOptionNameLastRun(), $timeNow);
        }
    }

    /**
     * @return false|mixed|void
     */
    public function getLastRun()
    {
        return get_option($this->getOptionNameLastRun());
    }

    private function getOptionNameLastRun(): string
    {
        return $this->hook . '_last_run';
    }

    abstract protected function work(DateTime $dateLastRun, DateTime $dateNow);

    public function switch(bool $active)
    {
        if ($active)
        {
            $this->activation();
        }
        else
        {
            $this->deactivate();
        }
    }

    public function activation()
    {
        if (!wp_next_scheduled($this->hook))
        {
            wp_schedule_event(time(), 'quarterly', $this->hook);
        }
    }

    public function deactivate()
    {
        $timestamp = wp_next_scheduled($this->hook);
        wp_unschedule_event($timestamp, $this->hook);
    }

    public function isActivated(): bool
    {
        return (bool)wp_next_scheduled($this->hook);
    }

    /**
     * @return mixed
     */
    public function getHook()
    {
        return $this->hook;
    }
}

