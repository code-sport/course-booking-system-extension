<?php

//defined('TEST');

add_filter('cron_schedules', 'cbse_add_cron_quarterly_interval');
function cbse_add_cron_quarterly_interval($schedules)
{
    $schedules['quarterly'] = array(
        'interval' => 900,
        'display' => esc_html__('Every 15 Minutes'),);
    return $schedules;
}


add_action('cbse_cron_quarterly_hook', 'cbse_cron_quarterly_exec');
function cbse_cron_quarterly_exec()
{

    $timeNow = time();
    $dateNow = new DateTime();
    $dateNow->setTimestamp($timeNow);
    $dateNow->setTimezone(wp_timezone());

    $lastRun = get_option('cbse_cron_quarterly_last_run');
    $dateLastRun = new DateTime();
    $dateLastRun->setTimestamp($lastRun);
    $dateLastRun->setTimezone(wp_timezone());

    if ($lastRun === false) {
        add_option('cbse_cron_quarterly_last_run', $timeNow);
    } else {
        cbse_cron_sent_mail_to_coach($dateLastRun, $dateNow);
        update_option('cbse_cron_quarterly_last_run', $timeNow);
    }
}

function cbse_cron_sent_mail_to_coach(DateTime $dateLastRun, DateTime $dateNow)
{
    $options = get_option('cbse_options');
    $hour = is_numeric($options['cron_before_time_hour']) && (int)$options['cron_before_time_hour'] > 0 && (int)$options['cron_before_time_hour'] < 24 ? $options['cron_before_time_hour'] : 2;
    $minute = is_numeric($options['cron_before_time_minute']) && (int)$options['cron_before_time_minute'] > 0 && (int)$options['cron_before_time_minute'] < 60 ? $options['cron_before_time_minute'] : 0;

    try {
        $interval = new DateInterval('PT' . $hour . 'H' . $minute . 'M');
    } catch (Exception $e) {
        $interval = new DateInterval('PT2H');
    }
    $dateFrom = clone $dateLastRun;
    $dateFrom->add($interval);
    $dateTo = clone $dateNow;
    $dateTo->add($interval);

    if (defined('TEST')) {
        wp_mail(get_option('admin_email'), 'CronTest', "Interval: $interval\nLast run: $dateLastRun");
    }

    $courses = cbse_courses_in_time($dateFrom, $dateTo);

    foreach ($courses as $course) {
        $userId = ($course->substitutes_user_id ?? $course->user_id);
        $autoInformWay = empty(get_the_author_meta('cbse-auto-inform', $userId)) ? 'email' : get_the_author_meta('cbse-auto-inform', $userId);

        if (defined('TEST')) {
            wp_mail(get_option('admin_email'), 'CronTest', "UserId: $userId\nInformway Way: $autoInformWay " . ($autoInformWay == 'email'));
        }

        if ($autoInformWay == 'email') {
            cbse_sent_mail_with_course_date_bookings($course->course_id, $course->date, $userId);
        }
    }
}

function cbse_cron_activation()
{
    $hook = 'cbse_cron_quarterly_hook';
    if (!wp_next_scheduled($hook)) {
        wp_schedule_event(time(), 'quarterly', $hook);
    }
}

function cbse_cron_deactivate()
{
    $hook = 'cbse_cron_quarterly_hook';
    $timestamp = wp_next_scheduled($hook);
    wp_unschedule_event($timestamp, $hook);
}


