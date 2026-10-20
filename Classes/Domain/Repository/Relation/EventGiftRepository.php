<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Domain\Repository\Relation;

use PSBits\Foundation\Utility\QueryUtility;
use PSBits\GiftLog\Domain\Demand\EventGiftDemand;
use PSBits\GiftLog\Domain\Model\Relation\EventGift;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class EventGiftRepository
 * @extends Repository<EventGift>
 *
 * @package PSBits\GiftLog\Domain\Repository\Relation
 */
class EventGiftRepository extends Repository
{
    /**
     * @return QueryResultInterface<int, EventGift>
     */
    public function findByDemand(EventGiftDemand $demand): QueryResultInterface
    {
        $constraints = [];
        $query       = $this->createQuery();

        if (null !== $demand->getContactEvent()) {
            $constraints[] = $query->equals('contactEvent', $demand->getContactEvent());
        } elseif (null !== $demand->getGiftLogEvent()) {
            $constraints[] = $query->equals('giftLogEvent', $demand->getGiftLogEvent());
        }

        if ([] !== $demand->getGifts()) {
            $orConstraints = [];

            foreach ($demand->getGifts() as $gift) {
                $orConstraints[] = $query->equals('gift', $gift);
            }

            $constraints[] = $query->logicalOr(...$orConstraints);
        }

        QueryUtility::applyConstraints($constraints, $query);

        return $query->execute();
    }
}
