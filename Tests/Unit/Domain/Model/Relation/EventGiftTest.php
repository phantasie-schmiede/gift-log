<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\Domain\Model\Relation;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Enum\EventType;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Domain\Model\Relation\EventGift;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class EventGiftTest
 *
 * @package PSBits\GiftLog\Tests\Unit\Domain\Model\Relation
 */
class EventGiftTest extends UnitTestCase
{
    #[Test]
    public function aContactEventReplacesAGiftLogEvent(): void
    {
        $relation     = (new EventGift())->setEvent(new GiftLogEvent());
        $contactEvent = new ContactEvent();

        $relation->setEvent($contactEvent);

        self::assertNull($relation->getGiftLogEvent());
        self::assertSame($contactEvent, $relation->getContactEvent());
        self::assertSame($contactEvent, $relation->getEvent());
    }

    #[Test]
    public function aGiftLogEventReplacesAContactEvent(): void
    {
        $relation     = (new EventGift())->setEvent(new ContactEvent());
        $giftLogEvent = new GiftLogEvent();

        $relation->setEvent($giftLogEvent);

        self::assertNull($relation->getContactEvent());
        self::assertSame($giftLogEvent, $relation->getGiftLogEvent());
    }

    #[Test]
    public function theGiftLogEventIsReturnedByTheGenericGetter(): void
    {
        $giftLogEvent = new GiftLogEvent();
        $relation     = (new EventGift())->setEvent($giftLogEvent);

        self::assertSame($giftLogEvent, $relation->getEvent());
    }

    #[Test]
    public function theGenericGetterReturnsNullWithoutAnEvent(): void
    {
        self::assertNull((new EventGift())->getEvent());
    }

    #[Test]
    public function theCompactValueContainsOnlyTheYearWithoutAnEvent(): void
    {
        $relation = (new EventGift())->setYear(2024);

        self::assertSame('(2024)', $relation->getCompactValue());
    }

    #[Test]
    public function theCompactValueIsBuiltFromTheGiftLogEvent(): void
    {
        $giftLogEvent = new GiftLogEvent();
        $giftLogEvent->setName('Christmas');
        $relation     = (new EventGift())
            ->setEvent($giftLogEvent)
            ->setYear(2024);

        self::assertSame('(2024) Christmas', $relation->getCompactValue());
    }

    #[Test]
    public function theCompactValueIsBuiltFromAContactEventWithNotes(): void
    {
        $contactEvent = new ContactEvent();
        $contactEvent->setType(EventType::Other);
        $contactEvent->setNotes('My party');
        $relation     = (new EventGift())
            ->setEvent($contactEvent)
            ->setYear(2024);

        self::assertSame('(2024) My party', $relation->getCompactValue());
    }
}
