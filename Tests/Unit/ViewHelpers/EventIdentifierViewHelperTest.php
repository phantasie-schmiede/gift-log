<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Service\EventService;
use PSBits\GiftLog\ViewHelpers\EventIdentifierViewHelper;
use ReflectionProperty;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class EventIdentifierViewHelperTest
 *
 * @package PSBits\GiftLog\Tests\Unit\ViewHelpers
 */
class EventIdentifierViewHelperTest extends UnitTestCase
{
    private static function entityWithUid(string $class, int $uid): \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
    {
        $entity = new $class();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($entity, $uid);

        return $entity;
    }

    private function createViewHelper(): EventIdentifierViewHelper
    {
        $viewHelper = new EventIdentifierViewHelper(new EventService());
        $viewHelper->initializeArguments();

        return $viewHelper;
    }

    #[Test]
    public function theEventArgumentIsRequired(): void
    {
        $definitions = $this->createViewHelper()
            ->prepareArguments();

        self::assertArrayHasKey('event', $definitions);
        self::assertTrue($definitions['event']->isRequired());
        self::assertSame('object', $definitions['event']->getType());
    }

    #[Test]
    public function theUniqueIdentifierOfAGiftLogEventIsRendered(): void
    {
        $viewHelper = $this->createViewHelper();
        $viewHelper->setArguments(['event' => self::entityWithUid(GiftLogEvent::class, 3)]);

        self::assertSame(GiftLogEvent::class . ':3', $viewHelper->render());
    }

    #[Test]
    public function theUniqueIdentifierOfAContactEventIsRendered(): void
    {
        $viewHelper = $this->createViewHelper();
        $viewHelper->setArguments(['event' => self::entityWithUid(ContactEvent::class, 7)]);

        self::assertSame(ContactEvent::class . ':7', $viewHelper->render());
    }
}
