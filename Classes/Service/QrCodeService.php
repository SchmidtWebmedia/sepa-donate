<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

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
        float $amount,
        string $purpose,
        int $size = 200,
    ): string {
        $payload = $this->buildEpcPayload(
            iban: $iban,
            bic: $bic,
            recipient: $recipient,
            amount: $amount,
            purpose: $purpose,
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
        float $amount,
        string $purpose,
    ): string {
        // EPC069-12: structured reference remains empty, purpose is unstructured remittance information.
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
            '',
            $purpose,
            '',
        ]);
    }
}
