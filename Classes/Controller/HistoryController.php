<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Controller;

use JsonException;
use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbFoundation\Attribute\PluginAction;
use PSB\PsbFoundation\Traits\ControllerCacheTrait;
use PSB\PsbGiftLog\Domain\Demand\EventGiftDemand;
use PSB\PsbGiftLog\Domain\Model\Event as GiftLogEvent;
use PSB\PsbGiftLog\Domain\Model\Relation\EventGift;
use PSB\PsbGiftLog\Domain\Repository\Relation\EventGiftRepository;
use PSB\PsbTemplates\Utility\StringUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class HistoryController
 *
 * @package PSB\PsbGiftLog\Controller
 */
class HistoryController extends ActionController
{
    use ControllerCacheTrait;

    public const string CACHE_TAG = 'tx_psbgiftlog';

    public function __construct(
        protected EventGiftRepository $eventGiftRepository,
    ) {
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
