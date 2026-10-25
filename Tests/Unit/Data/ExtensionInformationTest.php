<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use PSBits\Foundation\Data\PluginConfiguration;
use PSBits\GiftLog\Controller\GiftController;
use PSBits\GiftLog\Controller\HistoryController;
use PSBits\GiftLog\Data\ExtensionInformation;

final class ExtensionInformationTest extends TestCase
{
    public function testRegistersThePlugins(): void
    {
        $extensionInformation = new ExtensionInformation();

        self::assertSame([], $extensionInformation->getMainModules());
        self::assertSame([], $extensionInformation->getModules());
        self::assertSame([], $extensionInformation->getPageTypes());

        $plugins = $extensionInformation->getPlugins();

        self::assertCount(2, $plugins);
        self::assertContainsOnlyInstancesOf(PluginConfiguration::class, $plugins);

        $gift = $plugins[0];
        self::assertSame('Gift', $gift->getName());
        self::assertSame([GiftController::class], $gift->getControllers());

        $history = $plugins[1];
        self::assertSame('History', $history->getName());
        self::assertSame([HistoryController::class], $history->getControllers());
    }
}
