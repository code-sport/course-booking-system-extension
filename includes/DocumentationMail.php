<?php

namespace CBSE;

require_once 'Mail.php';

class DocumentationMail extends Mail
{
    private int $courseId;
    private string $date;
    private int $userId;

    public function __construct(int $courseId, string $date, int $userId)
    {
        $this->courseId = $courseId;
        $this->date = $date;
        $this->userId = $userId;
    }

    public function sent(): bool
    {
        return cbse_sent_mail_with_course_date_bookings($this->courseId, $this->date, $this->userId);
    }
}
