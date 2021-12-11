<?php

namespace CBSE;

use CBSE\Dto\CourseInfoDate;

class DocumentationMail extends Mail
{
    private CourseInfoDate $course;
    private $mailSettings;

    /**
     * @param CourseInfoDate $courseInfoDate
     */
    public function __construct(CourseInfoDate $courseInfoDate)
    {
        parent::__construct();
        $this->course = $courseInfoDate;
        $this->mailSettings = get_option('cbse_coach_mail_options');
    }

    public function sentToUser(int $userId): bool
    {
        $user = get_userdata($userId);

        $to = $this->getTo($user);
        $subject = $this->getSubject($this->mailSettings['subject']);
        $message = $this->getMessage($user);
        $headers = $this->getHeaders();
        $docuPDF = new DocumentationPdf($this->course);
        $docuPDF->generatePdf();
        $attachments = array($docuPDF->getPdfFile());

        $mailSent = wp_mail($to, $subject, $message, $headers, $attachments);
        $docuPDF->unlink();

        return $mailSent;
    }

    public function sentToPrinter(array $mailadresses): bool
    {

        $to = $mailadresses;
        $subject = $this->getSubject('PRINT');
        $message = '';
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
    private function getTo($user)
    {
        return $user->user_email;
    }

    /**
     * Email subject
     */
    private function getSubject($prefix): string
    {
        $subject = $prefix;
        $subject .= " | {$this->course->getCourseDateTimeString()}";
        $categories = $this->course->getEventCategoriesAsString('-');
        $subject .= " | {$categories}";
        $subject .= " | {$this->course->getEvent()->post_title}";

        return $subject;
    }

    /**
     *    Message contents
     *
     * @return string
     */
    private function getMessage($user): string
    {
        $message = $this->mailSettings['message'] ?? __("Hi %first_name%,\n\nplease note the file in the attachment.\n\nRegards\nYour IT.", CBSE_LANGUAGE_DOMAIN);
        $message = str_replace('%first_name%', $user->firstName, $message);
        $message = str_replace('%last_name%', $user->lastName, $message);
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
            foreach ($this->course->getBookingsAlphabeticallySortedByLastName() as $booking)
            {
                $messageReplace .= $bookingNumber . '. ' . trim($booking->lastName) . ', ' . trim($booking->firstName) . PHP_EOL;
                $bookingNumber++;
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
