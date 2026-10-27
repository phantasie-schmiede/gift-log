<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\Domain\Demand;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\GiftLog\Domain\Demand\EventGiftDemand;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Domain\Model\Gift;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class EventGiftDemandTest
 *
 * @package PSBits\GiftLog\Tests\Unit\Domain\Demand
 */
class EventGiftDemandTest extends UnitTestCase
{
    #[Test]
    public function aContactEventIsAssignedToTheContactEventSlot(): void
    {
        $contactEvent = new ContactEvent();
        $demand       = new EventGiftDemand();

        $demand->setEvent($contactEvent);

        self::assertSame($contactEvent, $demand->getContactEvent());
        self::assertNull($demand->getGiftLogEvent());
    }

    #[Test]
    public function aGiftLogEventIsAssignedToTheGiftLogEventSlot(): void
    {
        $giftLogEvent = new GiftLogEvent();
        $demand       = new EventGiftDemand();

        $demand->setEvent($giftLogEvent);

        self::assertNull($demand->getContactEvent());
        self::assertSame($giftLogEvent, $demand->getGiftLogEvent());
    }

    #[Test]
    public function theGiftsAreStored(): void
    {
        $demand = new EventGiftDemand();
        $gifts  = [new Gift(), new Gift()];

        $demand->setGifts($gifts);

        self::assertSame($gifts, $demand->getGifts());
    }

    #[Test]
    public function theDemandIsInitiallyEmpty(): void
    {
        $demand = new EventGiftDemand();

        self::assertNull($demand->getContactEvent());
        self::assertNull($demand->getGiftLogEvent());
        self::assertSame([], $demand->getGifts());
    }

    #[Test]
    public function theSpecificSettersAreFluent(): void
    {
        $demand       = new EventGiftDemand();
        $contactEvent = new ContactEvent();
        $giftLogEvent = new GiftLogEvent();

        self::assertSame($demand, $demand->setContactEvent($contactEvent));
        self::assertSame($demand, $demand->setGiftLogEvent($giftLogEvent));
        self::assertSame($demand, $demand->setGifts([]));
    }
}
