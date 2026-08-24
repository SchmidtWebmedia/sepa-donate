<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;

class MailService
{
    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    public function sendNotification(
        string $to,
        float $amount,
        string $purpose,
        array $address = [],
    ): void {
        if (empty($to)) {
            return;
        }

        $email = (new FluidEmail())
            ->to($to)
            ->subject(sprintf('Spende angekündigt — %s', $purpose))
            ->format(FluidEmail::FORMAT_PLAIN)
            ->setTemplate('Notification/DonationNotification')
            ->assignMultiple([
                'amount' => $amount,
                'purpose' => $purpose,
                'address' => $address,
                'date' => new \DateTimeImmutable(),
            ]);

        $this->mailer->send($email);
    }
}
