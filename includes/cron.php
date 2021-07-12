<?php

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
    $lastRun = get_option('cbse_cron_quarterly_last_run');
    $timeNow = time();
    $dateNow = new DateTime();
    $dateNow->setTimestamp($timeNow);
    $dateNow->setTimezone(wp_timezone());
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
    $interval = new DateInterval('PT1H');
    $dateFrom = clone $dateLastRun;
    $dateFrom->add($interval);
    $dateTo = clone $dateNow;
    $dateTo->add($interval);
    $dateDiff = date_diff($dateFrom, $dateTo);


    $to = 'wordpress@codesport.info';
    $subject = 'cbse_cron_quarterly_last_run';
    $message = 'Last run: ' . $dateLastRun->format(get_option('date_format') . ' ' . get_option('time_format')) . PHP_EOL;
    $message .= 'Now run: ' . $dateNow->format(get_option('date_format') . ' ' . get_option('time_format')) . PHP_EOL;
    $message .= 'Work on: ' . $dateFrom->format(get_option('date_format') . ' ' . get_option('time_format')) . ' - ' . $dateTo->format(get_option('date_format') . ' ' . get_option('time_format')) . PHP_EOL;

    $courses = array();

    // Courses on the same day
    if ($dateFrom->format('Y-m-d') == $dateTo->format('Y-m-d')) {
        array_push($courses, cbse_courses_in_time($dateFrom, $dateFrom, $dateTo));
    } else {
        $period = new DatePeriod(
            new DateTime($dateFrom->format('Y-m-d')),
            new DateInterval("P1D"),
            new DateTime($dateTo->format('Y-m-d')));

        $dates = array(); // TODO find a better way
        foreach ($period as $key => $value) {
            $dates[] = $value->format('Y-m-d');
        }

        array_push($courses, cbse_courses_in_time($dateFrom, $dateFrom, new DateTime($dates[0] . ' 23:59:59')));
        for ($i = 1; $i < count($dates); $i++) { // TODO Check what happens when only a day is between
            array_push($courses, cbse_courses_in_time($dateFrom, new DateTime($dates[$i] . ' 00:00:00'), new DateTime($dates[$i] . ' 23:59:59')));
        }
        array_push($courses, cbse_courses_in_time($dateFrom, new DateTime($dateTo->format('Y-m-d') . ' 00:00:00'), $dateTo));
    }

    foreach ($courses as $course) {
        cbse_sent_mail_with_course_date_bookings($course->course_id, $dateLastRun->format('Y-m-d'), ($course->substitutes_user_id ?? $course->user_id));
    }
    $message .= print_r((array)$courses, 1);
    wp_mail($to, $subject, $message);
}

register_activation_hook(__FILE__, 'cbse_cron_activation');
add_action('upgrader_process_complete', 'cbse_cron_activation', 10, 2);
function cbse_cron_activation()
{
    $hook = 'cbse_cron_quarterly_hook';
    if (!wp_next_scheduled($hook)) {
        wp_schedule_event(time(), 'quarterly', $hook);
    }
}

register_deactivation_hook(__FILE__, 'cbse_cron_deactivate');
function cbse_cron_deactivate()
{
    $timestamp = wp_next_scheduled('cbse_cron_quarterly_hook');
    wp_unschedule_event($timestamp, 'cbse_cron_quarterly_hook');
}


