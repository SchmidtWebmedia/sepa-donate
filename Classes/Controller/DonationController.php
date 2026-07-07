<?php

declare(strict_types=1);

namespace Schmidtwebmedia\SepaDonate\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DonationController extends ActionController
{
    public function formAction(): ResponseInterface
    {
        $this->view->assign('amounts', $this->getAmounts());

        return $this->htmlResponse();
    }

    private function getAmounts(): array
    {
        return array_map(
            'intval',
            explode(',', $this->settings['button_bar_amounts'] ?? '10,25,50,100')
        );
    }
}
