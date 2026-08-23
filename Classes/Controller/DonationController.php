<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DonationController extends ActionController
{
    private const ENDPOINT_PATH = '/api/sepa-donate/qr-code';

    public function formAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'amounts' => $this->getAmounts(),
            'endpointPath' => $this->getEndpointPath(),
        ]);

        return $this->htmlResponse();
    }

    private function getAmounts(): array
    {
        return array_map(
            'intval',
            explode(',', $this->settings['button_bar_amounts'] ?? '10,25,50,100')
        );
    }

    private function getEndpointPath(): string
    {
        $site = $this->request->getAttribute('site');
        if (!$site instanceof Site) {
            return self::ENDPOINT_PATH;
        }

        return rtrim($site->getBase()->getPath(), '/') . self::ENDPOINT_PATH;
    }
}
