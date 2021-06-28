<?php

function cbse_courses_for_head($userId)
{
    if (!is_int($userId) && $userId > 0) {
        throw new Exception("userID must be a positive int.");
    }

    global $wpdb;
    $query = "SELECT `id` AS `course_id`, `column_id`, `event_id`, `event_start`, `event_end`, `description`, `" . $wpdb->prefix . "mp_timetable_bookings`.`date` as date, COUNT(`" . $wpdb->prefix . "mp_timetable_bookings`.`booking_id`) AS bookings, COUNT(`" . $wpdb->prefix . "mp_timetable_waitlists`.`waitlist_id`) AS waitings, `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` AS substitutes_user_id, `note`";
    $query .= " FROM `" . $wpdb->prefix . "mp_timetable_data`";
    $query .= " JOIN `" . $wpdb->prefix . "mp_timetable_bookings` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_waitlists` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_waitlists`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_waitlists`.`date`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_notes` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_notes`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_notes`.`date`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_substitutes` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`date`";
    $query .= " WHERE (`" . $wpdb->prefix . "mp_timetable_data`.user_id = %d AND `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` IS NULL)OR `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` = %d";
    $query .= " GROUP BY  `" . $wpdb->prefix . "mp_timetable_bookings`.`date`, `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " ORDER BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date` ASC, `wp_mp_timetable_data`.`event_start` ASC;";
    $timeslots = $wpdb->get_results($wpdb->prepare($query, $userId, $userId));

    return $timeslots;
}

function cbse_course_date_bookings($courseId, $date)
{


    global $wpdb;
    $bookings_raw = $wpdb->get_results($wpdb->prepare("SELECT `booking_id`, `user_id` FROM `" . $wpdb->prefix . "mp_timetable_bookings` WHERE `course_id` =  %d AND `date` = %s;", $courseId, $date));
    $bookings = array();
    foreach ($bookings_raw as $booking) {
        $user_meta = get_userdata($booking->user_id);
        $booking->first_name = $user_meta->first_name;
        $booking->last_name = $user_meta->last_name;
        $booking->nickname = $user_meta->nickname;
        $bookings[] = $booking;
    }
    usort($bookings, fn($a, $b) => strcmp($a->last_name, $b->last_name));

    return $bookings;
}

function cbse_install_and_update()
{
    $dir = plugin_dir_path(__FILE__);
    if (!is_dir($dir . '../dependencies/fpdf')) {
        mkdir($dir . '../dependencies/fpdf', 0777, true);
    }

    if (!is_file($dir . '../dependencies/fpdf/fpdf.php')) {
        $url = 'http://www.fpdf.org/en/dl.php?v=183&f=zip';

        // Download
        $zip_filename = 'fpdf.zip';
        $fh = fopen($zip_filename, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
        curl_exec($ch);
        curl_close($ch);
        fclose($fh);

        // Extract
        $zip = new ZipArchive;
        if ($zip->open($zip_filename) === TRUE) {
            $zip->extractTo($dir . '../dependencies/fpdf');
            $zip->close();
        }

        // Delete download
        unlink($zip_filename);
    }
}
