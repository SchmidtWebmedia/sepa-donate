<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Service;

use TYPO3\CMS\Core\Site\Entity\Site;

final class SepaConfigurationProvider
{
    private const DEFAULT_AMOUNTS = [10, 25, 50, 100];

    /**
     * @return array{
     *     iban: string,
     *     bic: string,
     *     recipient: string,
     *     buttonBarAmounts: list<int>,
     *     mailTo: string
     * }
     */
    public function forSite(Site $site): array
    {
        $settings = $site->getSettings();

        return [
            'iban' => trim((string)$settings->get('sepaDonate.iban')),
            'bic' => trim((string)$settings->get('sepaDonate.bic')),
            'recipient' => trim((string)$settings->get('sepaDonate.recipient')),
            'buttonBarAmounts' => $this->parseAmounts(
                (string)$settings->get('sepaDonate.buttonBarAmounts')
            ),
            'mailTo' => trim((string)$settings->get('sepaDonate.mail.to')),
        ];
    }

    /**
     * @return list<int>
     */
    private function parseAmounts(string $configuredAmounts): array
    {
        $amounts = [];

        foreach (explode(',', $configuredAmounts) as $configuredAmount) {
            $amount = filter_var(
                trim($configuredAmount),
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($amount !== false) {
                $amounts[] = $amount;
            }
        }

        return $amounts !== []
            ? array_values(array_unique($amounts))
            : self::DEFAULT_AMOUNTS;
    }
}
