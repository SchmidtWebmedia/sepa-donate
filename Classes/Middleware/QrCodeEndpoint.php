<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Schmidtwebmedia\SepaDonate\Service\MailService;
use Schmidtwebmedia\SepaDonate\Service\QrCodeService;
use Schmidtwebmedia\SepaDonate\Service\ReferenceService;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Site\Entity\Site;

final class QrCodeEndpoint implements MiddlewareInterface
{
    private const ENDPOINT_PATH = '/api/sepa-donate/qr-code';

    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly MailService $mailService
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if ($request->getUri()->getPath() !== self::ENDPOINT_PATH) {
            return $handler->handle($request);
        }

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return new JsonResponse(['error' => 'Site not resolved'], 404);
        }

        $settings = $site->getSettings();
        $iban = (string)$settings->get('sepaDonate.iban');
        $bic = (string)$settings->get('sepaDonate.bic');
        $recipient = (string)$settings->get('sepaDonate.recipient');

        if ($iban === '' || $recipient === '') {
            return new JsonResponse(['error' => 'SEPA settings incomplete'], 500);
        }

        $params = $request->getParsedBody();
        $amount = (float)($params['amount'] ?? 0);
        $reference = ReferenceService::generate();
        $size = min(1024, max(64, (int)($params['size'] ?? 200)));

        if ($amount <= 0 || $reference === '') {
            return new JsonResponse(['error' => 'amount and referenz are required'], 400);
        }

        $svg = $this->qrCodeService->generate(
            iban: $iban,
            bic: $bic,
            recipient: $recipient,
            amount: $amount,
            reference: $reference,
            size: $size,
        );

        $this->mailService->sendNotification(
            to: $settings->get('sepaDonate.mail.to'),
            amount: $amount,
            reference: $reference,
            address: $params['withReceipt'] ? $params['address'] : []
        );

        return new JsonResponse([
            'qrCode' => base64_encode($svg),
            'reference' => $reference,
            'amount' => $amount
        ]);
    }
}
