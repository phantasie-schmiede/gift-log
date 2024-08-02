<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Repository;

use PSB\PsbFoundation\Utility\QueryUtility;
use PSB\PsbGiftLog\Domain\Demand\GiftDemand;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class GiftRepository
 *
 * @package PSB\PsbGiftLog\Domain\Repository
 */
class GiftRepository extends Repository
{
    /**
     * @throws InvalidQueryException
     */
    public function findByDemand(GiftDemand $demand): QueryResultInterface
    {
        $constraints = [];
        $query = $this->createQuery();

        if (null !== $demand->getRecipient()) {
            $constraints[] = $query->contains('recipients', $demand->getRecipient());
        }

        QueryUtility::applyConstraints($constraints, $query);

        return $query->execute();
    }
}
