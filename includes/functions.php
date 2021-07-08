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
    $query .= " ORDER BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date` ASC, `" . $wpdb->prefix . "mp_timetable_data`.`event_start` ASC;";
    $timeslots = $wpdb->get_results($wpdb->prepare($query, $userId, $userId));

    return $timeslots;
}

function cbse_course_info($courseId): stdClass
{
    if (!is_int($courseId)) {
        $courseId = intval($courseId);
    }

    global $wpdb;
    $courseInfo = new stdClass();
    $courseInfo->timeslot = $wpdb->get_row($wpdb->prepare("SELECT `column_id`, `event_id`, `event_start`, `event_end`, `description` FROM `" . $wpdb->prefix . "mp_timetable_data` WHERE `id` = %d;", $courseId));
    $courseInfo->event = get_post($courseInfo->timeslot->event_id);
    $courseInfo->event_meta = get_post($courseInfo->timeslot->event_id);
    $courseInfo->event_categories = get_the_terms($courseInfo->timeslot->event_id, 'mp-event_category');
    $courseInfo->column = get_post($courseInfo->timeslot->column_id);
    $courseInfo->column_meta = get_post($courseInfo->timeslot->column_id);

    return $courseInfo;
}

function cbse_course_date_bookings($courseId, $date, $pastdays = null, $futuredays = null): array
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
        do_action('qm/debug', 'PDF Libary is not availabe unter : {path}', ['path' => $fpdf_file]);
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


function cbse_sent_mail_with_course_date_bookings($courseId, $date, $userId)
{
    $pcpdf_file = cbse_get_tcpdf();
    if (!is_file($pcpdf_file)) {
        cbse_install_and_update();
    }
    require_once $pcpdf_file;
    require_once 'CBSE_PDF.php';

    $pdf_file = plugin_dir_path(__FILE__) . $courseId . '_' . $date . '.pdf';
    $user_meta = get_userdata($userId);
    $courseInfo = cbse_course_info($courseId);
    $courseInfo_categories = !empty($courseInfo->event_categories) ? implode(", ", array_column($courseInfo->event_categories, 'name')) : '';
    $bookings = cbse_course_date_bookings($courseId, $date);
    $date_string = date("d.m.Y", strtotime($date));
    $image_id = get_option('cbse_options')['header_image_attachment_id'];

    // Set some content to print
    $html = <<<EOD
    <style>    
    h1 {s
        text-align: center;
        font-size: 28pt;
    }
    </style>
EOD;
    $html .= wp_get_attachment_image($image_id, 700, "", array("class" => "img-responsive"));
    $html .= "<h1>" . get_option('cbse_options')['header_title'] . "</h1>";
    $html_teilnehmer = <<<EOD
    <h2 class="tableheader">TeilnehmerInnen:</h2>
EOD;

    $html_end = <<<EOD
     <style>    
        .signature {
            padding-top: 40px;
            border-bottom: 1px solid black;
            padding-bottom: 5px;
        }
    </style>
    <p class="signature">Unterschrift Trainer:</p>
EOD;

    //TODO Move into own class that a footer can be added
    // create new PDF document
    $pdf = new CBSE_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Code.Sport');
    $pdf->SetTitle("{$date_string} Dokumentation Sportbetrieb");
    $pdf->SetSubject("{$courseInfo_categories} - {$courseInfo->event->post_title}");

    // set default header data
    $pdf->setPrintHeader(false);
    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

    // set header and footer fonts
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
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

    $w = array(60, 140);
    $pdf->Cell($w[0], 6, "Sportart:", 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, $courseInfo_categories, 0, 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell($w[0], 6, "Datum und Zeit:", 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, "{$date_string} {$courseInfo->timeslot->event_start} - {$courseInfo->timeslot->event_end}", 0, 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell($w[0], 6, "Gruppe:", 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, $courseInfo->event->post_title, 0, 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell($w[0], 6, "Ort:", 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, "", 0, 0, 'L', false);
    $pdf->Ln();
    $pdf->Cell($w[0], 6, "Verantwortlicher TrainerIn:", 0, 0, 'L', false);
    $pdf->Cell($w[1], 6, "{$user_meta->last_name}, {$user_meta->first_name}", 0, 0, 'L', false);
    $pdf->Ln();

    $pdf->writeHTML($html_teilnehmer, true, false, true, false, 'L');

    // Table header
    $w = array(10, 80, 35, 55);

    $pdf->SetFillColor(79, 79, 79);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('', 'B');

    $pdf->Cell($w[0], 14, "", 1, 0, 'C', 1);
    $pdf->Cell($w[1], 14, "Name, Vorname (gut leserlich!)", 1, 0, 'C', 1);
    $pdf->Cell($w[2], 14, "", 1, 0, 'C', 1);
    $pdf->Cell($w[3], 14, "Unterschrift", 1, 0, 'C', 1);
    $pdf->Ln();

    //Table body
    $bookingNumber = 1;
    $fill = 0;

    $pdf->SetFillColor(237, 237, 237);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');

    foreach ($bookings as $booking) {
        $pdf->Cell($w[0], 12, $bookingNumber, 1, 0, 'R', $fill);
        $pdf->Cell($w[1], 12, $booking->last_name . ", " . $booking->first_name, 1, 0, 'L', $fill);
        $pdf->Cell($w[2], 12, "", 1, 0, 'C', $fill);
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

    $pdf->Ln(10);

    $pdf->writeHTML($html_end, true, false, true, false, '');

    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $pdf->Output($pdf_file, 'F');

    $user_info = get_userdata($userId);
    $to = $user_info->user_email;
    $subject = "Dokumentation Sportbetrieb - {$date_string} - {$courseInfo->timeslot->event_start} - {$courseInfo->timeslot->event_end} - {$courseInfo_categories} - {$courseInfo->event->post_title}";
    $message = "Hi {$user_meta->first_name}\n\nbitte die Datei in der Anlage beachten\n\nSportliche Grüße\nDeine IT.";
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
