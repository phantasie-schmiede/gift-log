<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Data;

use InvalidArgumentException;
use PSBits\Foundation\Data\AbstractExtensionInformation;
use PSBits\Foundation\Data\PluginConfiguration;
use PSBits\GiftLog\Controller\GiftController;
use PSBits\GiftLog\Controller\HistoryController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionInformation extends AbstractExtensionInformation
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct();
        $this->addPlugin(
            GeneralUtility::makeInstance(
                PluginConfiguration::class,
                controllers: [GiftController::class],
                name       : 'Gift'
            )
        );
        $this->addPlugin(
            GeneralUtility::makeInstance(
                PluginConfiguration::class,
                controllers: [HistoryController::class],
                name       : 'History'
            )
        );
    }
}
