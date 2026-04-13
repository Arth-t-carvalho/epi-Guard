<?php
declare(strict_types = 1)
;

namespace Facchini\Application\Service;

use Facchini\Domain\Entity\User;
use Facchini\Domain\Entity\Occurrence;

class NotificationService
{
    // Would inject MailerInterface, SMSInterface etc depending on needs

    public function __construct()
    {
    }

    public function notifyOccurrenceCreation(Occurrence $occurrence, array $usersToNotify): void
    {
    // Example logic:
    // foreach ($usersToNotify as $user) {
    //     $this->mailer->send($user->getEmail(), "New Occurrence Registered", "...");
    // }
    }

    public function notifyOccurrenceResolution(Occurrence $occurrence, array $usersToNotify): void
    {
    // Implementation for resolution notification
    }
}
