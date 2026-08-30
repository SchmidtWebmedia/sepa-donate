<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Controller;

use Psr\Http\Message\ResponseInterface;
use Schmidtwebmedia\SepaDonate\Service\FormTokenService;
use Schmidtwebmedia\SepaDonate\Service\SepaConfigurationProvider;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DonationController extends ActionController
{
    private const ENDPOINT_PATH = '/api/sepa-donate/qr-code';

    public function __construct(
        private readonly FormTokenService $formTokenService,
        private readonly SepaConfigurationProvider $configurationProvider,
    ) {}

    public function formAction(): ResponseInterface
    {
        $site = $this->request->getAttribute('site');
        $configuration = $site instanceof Site
            ? $this->configurationProvider->forSite($site)
            : [
                'iban' => '',
                'recipient' => '',
                'buttonBarAmounts' => [10, 25, 50, 100],
            ];

        $this->view->assignMultiple([
            'amounts' => $configuration['buttonBarAmounts'],
            'donationSettings' => [
                'iban' => $configuration['iban'],
                'recipient' => $configuration['recipient'],
            ],
            'endpointPath' => $this->getEndpointPath($site),
            'formToken' => $site instanceof Site ? $this->formTokenService->generate($site) : '',
        ]);

        return $this->htmlResponse();
    }

    private function getEndpointPath(mixed $site): string
    {
        if (!$site instanceof Site) {
            return self::ENDPOINT_PATH;
        }

        return rtrim($site->getBase()->getPath(), '/') . self::ENDPOINT_PATH;
    }
}
