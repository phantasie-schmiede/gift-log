<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Service;

use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbGiftLog\Domain\Model\Event as GiftLogEvent;

/**
 * Class EventService
 *
 * @package PSB\PsbGiftLog\Service
 */
readonly class EventService
{
    public function getUniqueIdentifier(ContactEvent|GiftLogEvent $event): string
    {
        return $event::class . ':' . $event->getUid();
    }
}
