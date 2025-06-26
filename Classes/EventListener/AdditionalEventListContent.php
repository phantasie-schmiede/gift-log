<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\EventListener;

use PSB\PsbContacts\Domain\Repository\EventRepository as ContactEventRepository;
use PSB\PsbContacts\Event\AdditionalEventListContentEvent;
use PSBits\Foundation\Utility\Configuration\FilePathUtility;
use PSBits\GiftLog\Data\ExtensionInformation;
use PSBits\GiftLog\Domain\Demand\EventGiftDemand;
use PSBits\GiftLog\Domain\Repository\Relation\EventGiftRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

/**
 * Class AdditionalEventListContent
 *
 * @package PSBits\GiftLog\EventListener
 */
#[AsEventListener(identifier: 'GiftLog/AdditionalEventListContent')]
readonly class AdditionalEventListContent
{
    public function __construct(
        protected ContactEventRepository $contactEventRepository,
        protected ExtensionInformation   $extensionInformation,
        protected EventGiftRepository    $eventGiftRepository,
        protected ViewFactoryInterface   $viewFactory,
    ) {
    }

    public function __invoke(AdditionalEventListContentEvent $event): void
    {
        $demand = (new EventGiftDemand())->setContactEvent($event->getEvent());

        $viewFactoryData = new ViewFactoryData(
               templatePathAndFilename: FilePathUtility::getPrivateResourcePath(
                $this->extensionInformation,
                'Templates/Event/AdditionalEventListContent.html'
            ), request                : $event->getRequest(),
        );
        $view = $this->viewFactory->create($viewFactoryData);

        $view->assignMultiple([
            // Transform an original event to an extended one.
            'event'    => $this->contactEventRepository->findByUid(
                $event->getEvent()
                    ->getUid()
            ),
            'hasGifts' => $this->eventGiftRepository->findByDemand($demand)
                    ?->count() > 0,
        ]);

        $event->addContent($view->render());
    }
}
