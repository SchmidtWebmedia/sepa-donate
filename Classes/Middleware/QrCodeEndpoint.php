<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Schmidtwebmedia\SepaDonate\Service\FormTokenService;
use Schmidtwebmedia\SepaDonate\Service\MailService;
use Schmidtwebmedia\SepaDonate\Service\PurposeService;
use Schmidtwebmedia\SepaDonate\Service\QrCodeService;
use Schmidtwebmedia\SepaDonate\Service\SepaConfigurationProvider;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Site\Entity\Site;

final class QrCodeEndpoint implements MiddlewareInterface
{
    private const ENDPOINT_PATH = '/api/sepa-donate/qr-code';
    private const RATE_LIMIT_SECONDS = 60;
    private const MAX_AMOUNT = 999999999.99;

    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly MailService $mailService,
        private readonly FormTokenService $formTokenService,
        private readonly SepaConfigurationProvider $configurationProvider,
        private readonly CacheManager $cacheManager,
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if (!str_ends_with($request->getUri()->getPath(), self::ENDPOINT_PATH)) {
            return $handler->handle($request);
        }

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return new JsonResponse(['error' => 'Site not resolved'], 404);
        }

        $expectedPath = rtrim($site->getBase()->getPath(), '/') . self::ENDPOINT_PATH;
        if ($request->getUri()->getPath() !== $expectedPath) {
            return $handler->handle($request);
        }

        if ($request->getMethod() !== 'POST') {
            return new JsonResponse(['error' => 'Method not allowed'], 405, ['Allow' => 'POST']);
        }

        if (!$this->hasValidOrigin($request)) {
            return new JsonResponse(['error' => 'Invalid origin'], 403);
        }

        $params = $request->getParsedBody();
        if (!is_array($params)) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        if (trim((string)($params['company'] ?? '')) !== '') {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        $formToken = (string)($params['formToken'] ?? '');
        if (!$this->formTokenService->isValid($formToken, $site)) {
            return new JsonResponse(['error' => 'Invalid or expired form'], 403);
        }

        if ($this->isRateLimitExceeded($request, $site)) {
            return new JsonResponse(
                ['error' => 'Too many requests'],
                429,
                ['Retry-After' => (string)self::RATE_LIMIT_SECONDS]
            );
        }

        if (!isset($params['amount']) || !is_numeric($params['amount'])) {
            return new JsonResponse(['error' => 'Valid amount is required'], 400);
        }

        $amount = (float)$params['amount'];
        if (!is_finite($amount) || $amount <= 0 || $amount > self::MAX_AMOUNT) {
            return new JsonResponse(['error' => 'Valid amount is required'], 400);
        }

        $configuration = $this->configurationProvider->forSite($site);
        $iban = $configuration['iban'];
        $bic = $configuration['bic'];
        $recipient = $configuration['recipient'];

        if ($iban === '' || $recipient === '') {
            return new JsonResponse(['error' => 'SEPA settings incomplete'], 500);
        }

        $withReceipt = !empty($params['withReceipt']);
        $address = $withReceipt ? $this->getAddress($params['address'] ?? null) : [];
        if ($address === null) {
            return new JsonResponse(['error' => 'Invalid address data'], 400);
        }

        $purpose = PurposeService::generate();
        $size = min(1024, max(64, (int)($params['size'] ?? 200)));

        $svg = $this->qrCodeService->generate(
            iban: $iban,
            bic: $bic,
            recipient: $recipient,
            amount: $amount,
            purpose: $purpose,
            size: $size,
        );

        $this->mailService->sendNotification(
            to: $configuration['mailTo'],
            amount: $amount,
            purpose: $purpose,
            address: $address,
        );

        $this->formTokenService->consume($formToken);

        return new JsonResponse([
            'qrCode' => base64_encode($svg),
            'purpose' => $purpose,
            'amount' => $amount,
        ]);
    }

    private function hasValidOrigin(ServerRequestInterface $request): bool
    {
        $origin = trim($request->getHeaderLine('Origin'));
        if ($origin === '') {
            return true;
        }

        $originParts = parse_url($origin);
        if (!is_array($originParts) || !isset($originParts['scheme'], $originParts['host'])) {
            return false;
        }

        $requestUri = $request->getUri();
        $originPort = $originParts['port'] ?? null;
        $requestPort = $requestUri->getPort();

        return strtolower($originParts['scheme']) === strtolower($requestUri->getScheme())
            && strtolower($originParts['host']) === strtolower($requestUri->getHost())
            && $originPort === $requestPort;
    }

    private function isRateLimitExceeded(ServerRequestInterface $request, Site $site): bool
    {
        $serverParams = $request->getServerParams();
        $remoteAddress = (string)($serverParams['REMOTE_ADDR'] ?? 'unknown');
        $cache = $this->cacheManager->getCache('hash');
        $cacheKey = 'sepa_donate_' . sha1($site->getIdentifier() . '|' . $remoteAddress);

        if ($cache->has($cacheKey)) {
            return true;
        }

        $cache->set($cacheKey, true, [], self::RATE_LIMIT_SECONDS);

        return false;
    }

    private function getAddress(mixed $address): ?array
    {
        if (!is_array($address)) {
            return null;
        }

        $allowedFields = ['firstname', 'lastname', 'street', 'postalcode', 'location', 'email'];
        $normalizedAddress = [];

        foreach ($allowedFields as $field) {
            $value = $address[$field] ?? '';
            if (!is_string($value) || mb_strlen($value) > 255) {
                return null;
            }

            $normalizedAddress[$field] = trim($value);
        }

        if (
            $normalizedAddress['email'] !== ''
            && filter_var($normalizedAddress['email'], FILTER_VALIDATE_EMAIL) === false
        ) {
            return null;
        }

        return $normalizedAddress;
    }
}
