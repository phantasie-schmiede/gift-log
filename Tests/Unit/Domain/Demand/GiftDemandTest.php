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
use PSBits\ContactBook\Domain\Model\Contact;
use PSBits\GiftLog\Domain\Demand\GiftDemand;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class GiftDemandTest
 *
 * @package PSBits\GiftLog\Tests\Unit\Domain\Demand
 */
class GiftDemandTest extends UnitTestCase
{
    #[Test]
    public function theContactIsInitiallyNull(): void
    {
        self::assertNull((new GiftDemand())->getContact());
    }

    #[Test]
    public function aContactCanBeStoredAndReplaced(): void
    {
        $demand = new GiftDemand();
        $first  = new Contact();
        $second = new Contact();

        $demand->setContact($first);
        self::assertSame($first, $demand->getContact());

        $demand->setContact($second);
        self::assertSame($second, $demand->getContact());
    }

    #[Test]
    public function theContactCanBeClearedAgain(): void
    {
        $demand = (new GiftDemand())->setContact(new Contact());

        $demand->setContact(null);

        self::assertNull($demand->getContact());
    }
}
