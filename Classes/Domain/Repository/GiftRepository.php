<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Domain\Repository;

use PSBits\Foundation\Utility\QueryUtility;
use PSBits\GiftLog\Domain\Demand\GiftDemand;
use PSBits\GiftLog\Domain\Model\Gift;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Gift>
 */
class GiftRepository extends Repository
{
    /**
     * @return QueryResultInterface<int, Gift>
     * @throws InvalidQueryException
     */
    public function findByDemand(GiftDemand $demand): QueryResultInterface
    {
        $constraints = [];
        $query       = $this->createQuery();

        if (null !== $demand->getContact()) {
            $constraints[] = $query->logicalOr(
                $query->contains('donors', $demand->getContact()),
                $query->contains('recipients', $demand->getContact()),
            );
        }

        QueryUtility::applyConstraints($constraints, $query);

        return $query->execute();
    }
}
