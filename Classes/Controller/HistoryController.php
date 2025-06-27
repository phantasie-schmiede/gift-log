<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Controller;

use JsonException;
use PSBits\ContactBook\Domain\Model\Contact;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\Foundation\Attribute\PluginAction;
use PSBits\Foundation\Traits\ControllerCacheTrait;
use PSBits\Foundation\Utility\LocalizationUtility;
use PSBits\GiftLog\Domain\Demand\EventGiftDemand;
use PSBits\GiftLog\Domain\Demand\GiftDemand;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Domain\Model\Relation\EventGift;
use PSBits\GiftLog\Domain\Repository\GiftRepository;
use PSBits\GiftLog\Domain\Repository\Relation\EventGiftRepository;
use PSBits\Templates\Utility\StringUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class HistoryController
 *
 * @package PSBits\GiftLog\Controller
 */
class HistoryController extends ActionController
{
    use ControllerCacheTrait;

    public const string CACHE_TAG = 'tx_giftlog';

    public function __construct(
        protected EventGiftRepository $eventGiftRepository,
        protected GiftRepository      $giftRepository,
    ) {
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws InvalidQueryException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[PluginAction]
    public function listForContactAction(Contact $contact): ResponseInterface
    {
        $this->setCacheTags(self::CACHE_TAG);
        $giftDemand = new GiftDemand();
        $giftDemand->setContact($contact);
        $gifts = $this->giftRepository->findByDemand($giftDemand)
            ->toArray();
        $relationDemand = new EventGiftDemand();
        $relationDemand->setGifts($gifts);

        $this->view->assignMultiple([
            'contact'                    => $contact,
            'giftRelationsGroupedByYear' => $this->groupRelations(
                $this->eventGiftRepository->findByDemand($relationDemand)
            ),
        ]);

        return $this->htmlResponse();
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[PluginAction(default: true)]
    public function listForEventAction(
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->setCacheTags(self::CACHE_TAG);
        $demand = new EventGiftDemand();
        $demand->setEvent($contactEvent ?? $giftLogEvent);

        $this->view->assignMultiple([
            'contactEvent'               => $contactEvent,
            'event'                      => $giftLogEvent ?? $contactEvent,
            'giftLogEvent'               => $giftLogEvent,
            'giftRelationsGroupedByYear' => $this->groupRelations($this->eventGiftRepository->findByDemand($demand)),
        ]);

        return $this->htmlResponse();
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function groupRelations(QueryResultInterface $eventGiftRelations): array
    {
        $groups = [];

        /** @var EventGift $eventGiftRelation */
        foreach ($eventGiftRelations as $eventGiftRelation) {
            $year = $eventGiftRelation->getYear();
            $labelParts = [];
            $groupKeyElements = [$year];

            foreach ($eventGiftRelation->getGift()
                         ->getEventRelations() as $eventRelation) {
                $event = $eventRelation->getEvent();

                if ($event instanceof ContactEvent) {
                    $eventName = $event->getLabel($eventRelation->getYear());
                    $eventName = LocalizationUtility::translate(
                        key      : 'LLL:EXT:psb_contacts/Resources/Private/Language/Frontend/EventWidget/display.xlf:eventTitle',
                        arguments: [
                            'persons' => $event->getContactsAsEnumeration(),
                            'type'    => $eventName,
                        ]
                    );
                    $labelParts[] = $eventName;
                } else {
                    $eventName = $event->getName();
                    $labelParts[] = $eventName . ' ' . $eventRelation->getYear();
                }

                $groupKeyElements[] = $eventRelation->getYear() . '_' . $eventName;
            }

            sort($groupKeyElements);
            $key = implode('-', $groupKeyElements);

            if (isset($groups[$year][$key]['relations'])) {
                $groups[$year][$key]['relations'][] = $eventGiftRelation;
            } else {
                $groups[$year][$key] = [
                    'label'     => StringUtility::createEnumeration($labelParts),
                    'relations' => [$eventGiftRelation],
                ];
            }
        }

        krsort($groups);

        return $groups;
    }
}
