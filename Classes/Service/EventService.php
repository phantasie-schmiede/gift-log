<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Service;

use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;

/**
 * Class EventService
 *
 * @package PSBits\GiftLog\Service
 */
readonly class EventService
{
    public function getUniqueIdentifier(ContactEvent|GiftLogEvent $event): string
    {
        return $event::class . ':' . $event->getUid();
    }
}
