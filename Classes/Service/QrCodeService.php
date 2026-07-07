<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public function generate(
        string $iban,
        string $bic,
        string $recipient,
        float  $amount,
        string $reference,
        int    $size = 200,
    ): string {
        $payload = $this->buildEpcPayload(
            iban: $iban,
            bic: $bic,
            recipient: $recipient,
            amount: $amount,
            reference: $reference
        );

        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd(),
        );

        $writer = new Writer($renderer);

        return $writer->writeString($payload);
    }

    private function buildEpcPayload(
        string $iban,
        string $bic,
        string $recipient,
        float  $amount,
        string $reference,
    ): string {
        // EPC069-12 Standard
        return implode("\n", [
            'BCD',
            '002',
            '1',
            'SCT',
            $bic,
            $recipient,
            $iban,
            'EUR' . number_format($amount, 2, '.', ''),
            '',
            $reference,
            '',
        ]);
    }
}
