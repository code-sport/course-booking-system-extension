<?php

function cbse_courses_for_head($userId, $pastdays = 7, $futuredays = 7)
{
    if (!is_int($userId) || $userId < 0) {
        throw new Exception("userID must be a positive int.");
    }

    /* if (!is_int($pastdays) || $pastdays < 0) {
         throw new Exception("pastdays ($pastdays) must be a positive int.");
     }

     if (!is_int($futuredays)  || $futuredays < 0) {
         throw new Exception("futuredays ($futuredays) must be a positive int.");
     }
    */

    global $wpdb;
    $query = "SELECT `id` AS `course_id`, `column_id`, `event_id`, `event_start`, `event_end`, `description`, `" . $wpdb->prefix . "mp_timetable_bookings`.`date` as date, COUNT(`" . $wpdb->prefix . "mp_timetable_bookings`.`booking_id`) AS bookings, COUNT(`" . $wpdb->prefix . "mp_timetable_waitlists`.`waitlist_id`) AS waitings, `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` AS substitutes_user_id, `note`";
    $query .= " FROM `" . $wpdb->prefix . "mp_timetable_data`";
    $query .= " JOIN `" . $wpdb->prefix . "mp_timetable_bookings` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_waitlists` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_waitlists`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_waitlists`.`date`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_notes` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_notes`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_notes`.`date`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_substitutes` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`date`";
    $query .= " WHERE ((`" . $wpdb->prefix . "mp_timetable_data`.user_id = %d AND `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` IS NULL)OR `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` = %d)";
    $query .= " AND DATE(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`) > (NOW() - INTERVAL %d DAY)";
    $query .= " AND DATE(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`) < (NOW() + INTERVAL %d DAY)";
    $query .= " GROUP BY  `" . $wpdb->prefix . "mp_timetable_bookings`.`date`, `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " ORDER BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date` ASC, `" . $wpdb->prefix . "mp_timetable_data`.`event_start` ASC;";
    $timeslots = $wpdb->get_results($wpdb->prepare($query, $userId, $userId, $pastdays, $futuredays));

    return $timeslots;
}

function cbse_course_info($courseId, $date): stdClass
{
    if (!is_int($courseId)) {
        $courseId = intval($courseId);
    }

    global $wpdb;
    $courseInfo = new stdClass();
    // TODO extract in extra method to avoid duplicated calling
    $courseInfo->timeslot = $wpdb->get_row($wpdb->prepare("SELECT `column_id`, `event_id`, `event_start`, `event_end`, `description`, `user_id` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `id` = %d;", $courseId));
    $courseInfo->event = get_post($courseInfo->timeslot->event_id);
    $courseInfo->event_meta = get_post($courseInfo->timeslot->event_id);
    $courseInfo->event_categories = get_the_terms($courseInfo->timeslot->event_id, 'mp-event_category');
    $courseInfo->event_tags = get_the_terms($courseInfo->timeslot->event_id, 'mp-event_tag');
    $courseInfo->column = get_post($courseInfo->timeslot->column_id);
    $courseInfo->column_meta = get_post($courseInfo->timeslot->column_id);
    $courseInfo->substitutes = $wpdb->get_row($wpdb->prepare("SELECT `user_id` FROM `" . $wpdb->prefix . "mp_timetable_substitutes` WHERE `course_id` = %d AND `date` = '%s';", $courseId, $date));

    return $courseInfo;
}

function cbse_course_date_bookings($courseId, $date): array
{
    global $wpdb;
    //TODO past and future days
    $bookings_raw = $wpdb->get_results($wpdb->prepare("SELECT `booking_id`, `user_id` FROM `" . $wpdb->prefix . "mp_timetable_bookings` WHERE `course_id` =  %d AND `date` = %s;", $courseId, $date));
    $bookings = array();
    foreach ($bookings_raw as $booking) {
        $user_meta = get_userdata($booking->user_id);
        $booking->first_name = $user_meta->first_name;
        $booking->last_name = $user_meta->last_name;
        $booking->nickname = $user_meta->nickname;
        $booking->covid19_status = __(get_the_author_meta('covid-19-status', $booking->user_id));
        // TODO Validate status with date
        $bookings[] = $booking;
    }
    usort($bookings, fn($a, $b) => strcmp($a->last_name, $b->last_name));

    return $bookings;
}

function cbse_get_tcpdf()
{
    $tcpdf_folder = plugin_dir_path(__FILE__) . '../dependencies';

    $scan = scandir($tcpdf_folder);
    foreach ($scan as $scan_file) {
        if (substr($scan_file, 0, 6) === "TCPDF-") { //TODO Check if is a dictionary
            $tcpdf_folder .= '/' . $scan_file;
            break;
        }
    }

    return $tcpdf_folder . '/tcpdf.php';
}

function cbse_install_and_update()
{
    $tcpdf_Folder = plugin_dir_path(__FILE__) . '../dependencies/';
    if (!is_dir($tcpdf_Folder)) {
        mkdir($tcpdf_Folder, 0777, true);
    }

    $fpdf_file = cbse_get_tcpdf();
    if (!is_file($fpdf_file)) {
        do_action('qm/debug', 'PDF library is not available under : {path}', ['path' => $fpdf_file]);
        $url = 'https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.4.1.zip';
        $zip_filename = 'TCPDF.zip';

        // WordPress Download
        $response = wp_remote_get($url, array(
            'timeout' => 120,
        ));
        do_action('qm/debug', 'wp_remote_get: {response}', ['response' => $response]);
        $body = wp_remote_retrieve_body($response);
        // Write the file using put_contents instead of fopen(), etc.
        $wp_filesystem = cbse_get_wp_filesystem();

        $wp_filesystem->put_contents($zip_filename, $body);

        // Extract
        $result = unzip_file($zip_filename, $tcpdf_Folder);
        if (is_wp_error($result)) {
            do_action('qm/error', 'Could not extract TCPDF');
        }
        // Delete download
        unlink($zip_filename);
    }
}

function cbse_helper_array_exclude_and_column($array, $exclude, $filter)
{
    $excludes = explode(',', $exclude);
    $array_filtered = array_filter($array, function ($val) use ($excludes) {
        // Fatal error: Uncaught Error: Cannot use object of type WP_Term as array
        $values = $val->to_array();
        $id = $values['term_id'];
        return (!in_array($id, $excludes));
    });
    return array_column($array_filtered, $filter);
}

/**
 * Selects the courses on a specific date in a specific time.
 *
 * @param DateTime $timeFrom
 * @param DateTime $timeTo
 * @return array|object|null
 */
function cbse_courses_in_time(DateTime $timeFrom, DateTime $timeTo)
{
    global $wpdb;

    $query = "SELECT `id` AS `course_id`, `column_id`, `event_id`, `event_start`, `event_end`, `" . $wpdb->prefix . "mp_timetable_bookings`.`date` as date, `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` AS substitutes_user_id, `" . $wpdb->prefix . "mp_timetable_data`.user_id, TIMESTAMP(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`,`event_start`) as event_start_timestamp";
    $query .= " FROM `" . $wpdb->prefix . "mp_timetable_data`";
    $query .= " JOIN `" . $wpdb->prefix . "mp_timetable_bookings`";
    $query .= 'ON `' . $wpdb->prefix . 'mp_timetable_data`.`id` = `wp_mp_timetable_bookings`.`course_id`';
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_substitutes`";
    $query .= " ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`course_id`";
    $query .= " AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`date`";
    $query .= "WHERE TIMESTAMP(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`,`event_start`) >= '%s'";
    $query .= "AND  TIMESTAMP(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`,`event_start`) < '%s'";
    $query .= " GROUP BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date`, `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " ORDER BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date` ASC, `" . $wpdb->prefix . "mp_timetable_data`.`event_start` ASC;";
    return $wpdb->get_results($wpdb->prepare($query, $timeFrom->format('Y-m-d H:i:s'), $timeTo->format('Y-m-d H:i:s')));
}

function cbse_sent_mail_with_course_date_bookings($courseId, $date, $userId)
{
    $pcpdf_file = cbse_get_tcpdf();
    if (!is_file($pcpdf_file)) {
        cbse_install_and_update();
    }
    require_once $pcpdf_file;
    require_once 'CBSE_PDF.php';

    $cbse_options = get_option('cbse_options');

    $pdf_file = plugin_dir_path(__FILE__) . $courseId . '_' . $date . '.pdf';

    $courseInfo = cbse_course_info($courseId, $date);
    $courseInfo_categories = !empty($courseInfo->event_categories) ? implode(", ", cbse_helper_array_exclude_and_column($courseInfo->event_categories, $cbse_options['mail_categories_exclude'], 'name')) : '';
    $courseInfo_tags = !empty($courseInfo->event_tags) ? implode(", ", cbse_helper_array_exclude_and_column($courseInfo->event_tags, $cbse_options['mail_tags_exclude'], 'name')) : '';
    $user_meta_course = get_userdata($courseInfo->substitutes->user_id ?? $courseInfo->timeslot->user_id);
    $bookings = cbse_course_date_bookings($courseId, $date);
    $date_string = date(get_option('date_format'), strtotime($date));
    $time_start_string = date(get_option('time_format'), strtotime($courseInfo->timeslot->event_start));
    $time_end_string = date(get_option('time_format'), strtotime($courseInfo->timeslot->event_end));
    $courseInfo_DateTime = "{$date_string} {$time_start_string} - {$time_end_string}";
    $image_id = get_option('cbse_options')['header_image_attachment_id'];

    // Set some content to print
    $html = <<<EOD
    <style>    
    h1 {
        text-align: center;
        font-size: 28pt;
    }
    </style>
EOD;
    $html .= wp_get_attachment_image($image_id, 700, "", array("class" => "img-responsive"));
    $html .= "<h1>" . get_option('cbse_options')['header_title'] . "</h1>";
    $html_attendees = '<h2>' . __('Participants', 'course-booking-system-extension') ':</h2>';


    //TODO Move into own class that a footer can be added
    // create new PDF document
    $pdf = new CBSE_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Code.Sport');
    $pdf->SetTitle("$date_string " . get_option('cbse_options')['header_title']);
    $pdf->SetSubject("{$courseInfo_categories} | {$courseInfo->event->post_title} | {$courseInfo_DateTime}");

    // set default header data
    $pdf->setPrintHeader(false);
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterText("{$courseInfo->event->post_title} | {$courseInfo_DateTime}");

    // set header and footer fonts
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_LEFT);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // Set font
    // dejavusans is a UTF-8 Unicode font, if you only need to
    // print standard ASCII chars, you can use core fonts like
    // helvetica or times to reduce file size.
    $pdf->SetFont('dejavusans', '', 10, '', true);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();

    // Print text using writeHTML()
    $pdf->writeHTML($html, true, false, true, false, '');


    $w = array(55, 125);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Cell($w[0], 6, __('Date and Time', 'course-booking-system-extension') . ':', 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, "$courseInfo_DateTime", 0, 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell($w[0], 6, __('Title', 'course-booking-system-extension') . ':', 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, $courseInfo->event->post_title, 0, 0, 'L', false);
    $pdf->Ln();
    if (!empty($courseInfo->timeslot->description)) {
        $pdf->Cell($w[0], 6, __('Description', 'course-booking-system-extension') . ':', 0, 0, 'L', false);
        $pdf->Cell($w[1], 6, $courseInfo->timeslot->description, 0, 0, 'L', false);
        $pdf->Ln();
    }
    if ($cbse_options['mail_categories_exclude'] != '0') {
        $pdf->Cell($w[0], 6, ($cbse_options['mail_categories_title'] ?? __('Categories', 'course-booking-system-extension')) . ':', 0, 0, 'L', false);
        $pdf->Cell($w[1], 6, $courseInfo_categories, 0, 0, 'L', false);
        $pdf->Ln();
    }
    if ($cbse_options['mail_tags_exclude'] != '0') {
        $pdf->Cell($w[0], 6, ($cbse_options['mail_tags_title'] ?? __('Tags', 'course-booking-system-extension')) . ':', 0, 0, 'L', false);
        $pdf->Cell($w[1], 6, $courseInfo_tags, 0, 0, 'L', false);
        $pdf->Ln();
    }
    $pdf->Cell($w[0], 6, __('Responsible coach', 'course-booking-system-extension') . ':', 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, "{$user_meta_course->last_name}, {$user_meta_course->first_name}", 0, 0, 'L', false);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Cell($w[0], 6, __('Signature coach', 'course-booking-system-extension') . ':', 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, "", 'B', 0, 'L', false);

    $pdf->Ln();
    $pdf->Ln();
    $pdf->writeHTML($html_attendees, true, false, true, false, 'L');

    // Table header
    $w = array(10, 80, 35, 55);

    $pdf->SetFillColor(79, 79, 79);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('', 'B');

    $pdf->Cell($w[0], 14, "", 1, 0, 'C', 1);
    $pdf->Cell($w[1], 14, __('Surname, Firstname (legible!)', 'course-booking-system-extension'), 1, 0, 'C', 1);
    $pdf->Cell($w[2], 14, "", 1, 0, 'C', 1);
    $pdf->Cell($w[3], 14, __('Signature', 'course-booking-system-extension'), 1, 0, 'C', 1);
    $pdf->Ln();

    //Table body
    $bookingNumber = 1; //TODO replace with count($bookings)
    $bookingNames = array();
    $fill = 0;

    $pdf->SetFillColor(237, 237, 237);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');

    foreach ($bookings as $booking) {
        $bookingNames[] = $bookingNumber . '. ' . trim($booking->last_name) . ', ' . trim($booking->first_name); // For mail

        $pdf->Cell($w[0], 12, $bookingNumber, 1, 0, 'R', $fill);
        $pdf->Cell($w[1], 12, trim($booking->last_name) . ", " . trim($booking->first_name), 1, 0, 'L', $fill);
        $pdf->Cell($w[2], 12, __($booking->covid19_status), 1, 0, 'C', $fill);
        $pdf->Cell($w[3], 12, "", 1, 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;
        $bookingNumber++;
    }
    for ($i = $bookingNumber; $i <= $courseInfo->event_meta->attendance; $i++) {
        $pdf->Cell($w[0], 12, $i, 1, 0, 'R', $fill);
        $pdf->Cell($w[1], 12, "", 1, 0, 'L', $fill);
        $pdf->Cell($w[2], 12, "", 1, 0, 'C', $fill);
        $pdf->Cell($w[3], 12, "", 1, 0, 'C', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $pdf->Output($pdf_file, 'F');

    $user_info = get_userdata($userId);

    $to = $user_info->user_email;
    $subject = $cbse_options['header_title'] . " | {$courseInfo_DateTime} | {$courseInfo_categories} | {$courseInfo->event->post_title}";

    $message = $cbse_options['mail_coach_message'] ?? __("Hi %first_name%,\n\nplease note the file in the attachment.\n\nRegards\nYour IT.", 'course-booking-system-extension');
    $message = str_replace('%first_name%', $user_info->first_name, $message);
    $message = str_replace('%last_name%', $user_info->last_name, $message);
    $message = str_replace('%course_date%', $date_string, $message);
    $message = str_replace('%course_start%', $time_start_string, $message);
    $message = str_replace('%course_end%', $time_end_string, $message);
    $message = str_replace('%course_title%', $courseInfo->event->post_title, $message);
    $message = str_replace('%number_of_bookings%', count($bookings), $message);
    $message = str_replace('%maximum_participants%', $courseInfo->event_meta->attendance, $message);

    if (strpos($message, '%booking_names%') !== false) {
        $messageReplace = '';
        foreach ($bookingNames as $bookingName) {
            $messageReplace .= $bookingName . PHP_EOL;
        }
        $message = str_replace('%booking_names%', $messageReplace, $message);
    }

    $headers = "";
    $attachments = array($pdf_file);

    $mail_sent = wp_mail($to, $subject, $message, $headers, $attachments);

    unlink($pdf_file);

    return $mail_sent;

}

function cbse_get_wp_filesystem()
{
    global $wp_filesystem;

    if (is_null($wp_filesystem)) {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
    }

    return $wp_filesystem;
}
