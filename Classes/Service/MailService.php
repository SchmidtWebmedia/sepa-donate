<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;

class MailService
{
    public function __construct(
        private readonly Mailer $mailer,
    ) {}

    public function sendNotification(
        string $to,
        float  $amount,
        string $reference,
        array  $address = [],
    ): void {
        if (empty($to)) {
            return;
        }

        $email = (new FluidEmail())
            ->to($to)
            ->subject(sprintf('Mögliche Spende eingegangen — %s', $reference))
            ->format(FluidEmail::FORMAT_PLAIN)
            ->setTemplate('Notification/DonationNotification')
            ->assignMultiple([
                'amount'   => $amount,
                'reference' => $reference,
                'address'  => $address,
                'date'    => new \DateTimeImmutable(),
            ]);

        $this->mailer->send($email);
    }
}
