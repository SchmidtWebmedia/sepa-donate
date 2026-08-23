<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Site\Entity\Site;

class FormTokenService
{
    private const TOKEN_TTL = 1800;
    private const MINIMUM_TOKEN_AGE = 2;

    public function __construct(
        private readonly CacheManager $cacheManager,
    ) {}

    public function generate(Site $site): string
    {
        $token = bin2hex(random_bytes(32));

        $this->cacheManager->getCache('hash')->set(
            $this->getCacheKey($token),
            [
                'site' => $site->getIdentifier(),
                'createdAt' => time(),
            ],
            [],
            self::TOKEN_TTL,
        );

        return $token;
    }

    public function isValid(string $token, Site $site): bool
    {
        if ($token === '') {
            return false;
        }

        $cache = $this->cacheManager->getCache('hash');
        $data = $cache->get($this->getCacheKey($token));

        if (!is_array($data)) {
            return false;
        }

        if (($data['site'] ?? null) !== $site->getIdentifier()) {
            return false;
        }

        $createdAt = (int)($data['createdAt'] ?? 0);

        return $createdAt > 0 && time() - $createdAt >= self::MINIMUM_TOKEN_AGE;
    }

    public function consume(string $token): void
    {
        $this->cacheManager->getCache('hash')->remove($this->getCacheKey($token));
    }

    private function getCacheKey(string $token): string
    {
        return 'sepa_donate_form_' . sha1($token);
    }
}
