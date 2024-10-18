<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Data;

use PSB\PsbFoundation\Data\AbstractExtensionInformation;
use PSB\PsbFoundation\Data\PluginConfiguration;
use PSB\PsbGiftLog\Controller\HistoryController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtensionInformation
 *
 * @package PSB\PsbGiftLog\Data
 */
class ExtensionInformation extends AbstractExtensionInformation
{
    public function __construct()
    {
        parent::__construct();
        $this->addPlugin(
            GeneralUtility::makeInstance(
                PluginConfiguration::class,
                controllers: [HistoryController::class],
                name       : 'History'
            )
        );
    }
}
