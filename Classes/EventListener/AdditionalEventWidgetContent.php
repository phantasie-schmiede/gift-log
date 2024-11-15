<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\EventListener;

use PSB\PsbContacts\Domain\Repository\EventRepository as ContactEventRepository;
use PSB\PsbContacts\Event\AdditionalEventWidgetContentEvent;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbGiftLog\Data\ExtensionInformation;
use PSB\PsbGiftLog\Domain\Demand\EventGiftDemand;
use PSB\PsbGiftLog\Domain\Repository\Relation\EventGiftRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

/**
 * Class AdditionalEventWidgetContent
 *
 * @package PSB\PsbGiftLog\EventListener
 */
#[AsEventListener(identifier: 'PsbGiftLog/AdditionalEventWidgetContent')]
readonly class AdditionalEventWidgetContent
{
    public function __construct(
        protected ContactEventRepository $contactEventRepository,
        protected ExtensionInformation   $extensionInformation,
        protected EventGiftRepository    $eventGiftRepository,
        protected ViewFactoryInterface   $viewFactory,
    ) {
    }

    public function __invoke(AdditionalEventWidgetContentEvent $event): void
    {
        $demand = (new EventGiftDemand())->setContactEvent($event->getEvent());

        $viewFactoryData = new ViewFactoryData(
               templatePathAndFilename: FilePathUtility::getPrivateResourcePath(
                $this->extensionInformation,
                'Templates/Event/AdditionalEventWidgetContent.html'
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
