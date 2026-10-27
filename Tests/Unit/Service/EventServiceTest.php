<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Service\EventService;
use ReflectionProperty;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class EventServiceTest
 *
 * @package PSBits\GiftLog\Tests\Unit\Service
 */
class EventServiceTest extends UnitTestCase
{
    private static function entityWithUid(string $class, int $uid): \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
        $entity = new $class();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($entity, $uid);

        return $entity;
    }

    #[Test]
    public function theIdentifierCombinesClassAndUidOfAGiftLogEvent(): void
    {
        $event = self::entityWithUid(GiftLogEvent::class, 3);

        self::assertSame(GiftLogEvent::class . ':3', (new EventService())->getUniqueIdentifier($event));
    }

    #[Test]
    public function theIdentifierCombinesClassAndUidOfAContactEvent(): void
    {
        $event = self::entityWithUid(ContactEvent::class, 7);

        self::assertSame(ContactEvent::class . ':7', (new EventService())->getUniqueIdentifier($event));
    }
}
