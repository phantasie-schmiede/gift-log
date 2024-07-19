<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\EventListener;

use PSB\PsbContacts\Event\AdditionalContactContentEvent;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbGiftLog\Data\ExtensionInformation;
use PSB\PsbGiftLog\Domain\Demand\GiftDemand;
use PSB\PsbGiftLog\Domain\Repository\GiftRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ExtensionInformation
 *
 * @package PSB\PsbGiftLog\Data
 */
class AdditionalContactContent
{
    public function __construct(
        protected readonly ExtensionInformation $extensionInformation,
        protected readonly GiftRepository       $giftRepository,
    ) {
    }

    public function __invoke(AdditionalContactContentEvent $event): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $demand = (new GiftDemand())->setRecipient($event->getContact());

        $view->setTemplatePathAndFilename(
            FilePathUtility::getPrivateResourcePath(
                $this->extensionInformation,
                'Templates/Event/AdditionalContactContent.html'
            )
        );
        $view->assignMultiple([
            'gifts' => $this->giftRepository->findAll(),
        ]);

        $event->addContent($view->render());
    }
}
