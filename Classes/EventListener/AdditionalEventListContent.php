<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\EventListener;

use JsonException;
use PSBits\ContactBook\Domain\Repository\EventRepository as ContactEventRepository;
use PSBits\ContactBook\Event\AdditionalEventListContentEvent;
use PSBits\FrontendTemplates\Service\ViewService;
use PSBits\GiftLog\Data\ExtensionInformation;
use PSBits\GiftLog\Domain\Demand\EventGiftDemand;
use PSBits\GiftLog\Domain\Repository\Relation\EventGiftRepository;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(identifier: 'GiftLog/AdditionalEventListContent')]
readonly class AdditionalEventListContent
{
    public function __construct(
        protected ContactEventRepository $contactEventRepository,
        protected ExtensionInformation   $extensionInformation,
        protected EventGiftRepository    $eventGiftRepository,
        protected ViewService            $viewService,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __invoke(AdditionalEventListContentEvent $event): void
    {
        $demand = (new EventGiftDemand())->setContactEvent($event->getEvent());
        $view   = $this->viewService->createStandaloneView(
            $this->extensionInformation,
            $event->getRequest(),
            'Event/AdditionalEventListContent'
        );

        $view->assignMultiple([
            // Transform an original event to an extended one.
            'event'    => $this->contactEventRepository->findByUid(
                $event->getEvent()
                    ->getUid()
            ),
            'hasGifts' => 0 < $this->eventGiftRepository->findByDemand($demand)
                    ->count(),
        ]);

        $event->addContent($view->render());
    }
}
