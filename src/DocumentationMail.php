<?php

namespace CBSE;

use CBSE\Dto\CourseInfoDate;

class DocumentationMail extends Mail
{
    private CourseInfoDate $course;
    private $user;
    private $mailSettings;

    /**
     * @param CourseInfoDate $courseInfoDate
     * @param int            $userId
     */
    public function __construct(CourseInfoDate $courseInfoDate, int $userId)
    {
        parent::__construct();
        $this->course = $courseInfoDate;
        $this->user = get_userdata($userId);
        $this->mailSettings = get_option('cbse_coach_mail_options');
    }

    public function sent(): bool
    {
        $to = $this->getTo();
        $subject = $this->getSubject();
        $message = $this->getMessage();
        $headers = $this->getHeaders();
        $docuPDF = new DocumentationPdf($this->course);
        $docuPDF->generatePdf();
        $attachments = array($docuPDF->getPdfFile());

        $mailSent = wp_mail($to, $subject, $message, $headers, $attachments);
        $docuPDF->unlink();


        return $mailSent;
    }

    /**
     * Array or comma-separated list of email addresses to send message.
     *
     * @return string|array
     */
    private function getTo()
    {
        return $this->user->user_email;
    }

    /**
     * Email subject
     */
    private function getSubject(): string
    {
        $subject = $this->mailSettings['subject'];
        $subject .= " | {$this->course->getCourseDateTimeString()}";
        $categories = !empty($this->course->getEventCategories()) ? implode(", ", $this->course->getEventCategories()) : '-';
        $subject .= " | {$categories}";
        $subject .= " | {$this->course->getEvent()->post_title}";

        return $subject;
    }

    /**
     *    Message contents
     *
     * @return string
     */
    private function getMessage(): string
    {
        $message = $this->mailSettings['message'] ?? __("Hi %first_name%,\n\nplease note the file in the attachment.\n\nRegards\nYour IT.", CBSE_LANGUAGE_DOMAIN);
        $message = str_replace('%first_name%', $this->user->firstName, $message);
        $message = str_replace('%last_name%', $this->user->lastName, $message);
        $message = str_replace('%course_date%', $this->course->getCourseDateString(), $message);
        $message = str_replace('%course_start%', $this->course->getCourseStartTimeString(), $message);
        $message = str_replace('%course_end%', $this->course->getCourseEndTimeString(), $message);
        $message = str_replace('%course_title%', $this->course->getEvent()->post_title, $message);
        $message = str_replace('%number_of_bookings%', count($this->course->getBookings()), $message);
        $message = str_replace('%maximum_participants%', $this->course->getEventMeta()->attendance, $message);

        if (strpos($message, '%booking_names%') !== false)
        {
            $messageReplace = '';
            $bookingNumber = 1;
            foreach ($this->course->getBookings() as $booking)
            {
                $messageReplace .= $bookingNumber . '. ' . trim($booking->lastName) . ', ' . trim($booking->firstName) . PHP_EOL;
            }
            $message = str_replace('%booking_names%', $messageReplace, $message);
        }

        return $message;
    }

    /**
     * Optional. Additional headers.
     *
     * @return string|array
     */
    private function getHeaders()
    {
        return "";
    }
}
