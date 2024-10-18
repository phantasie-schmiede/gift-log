<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Model;

use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Interface EventWithGiftsInterface
 *
 * @package PSB\PsbGiftLog\Domain\Model
 */
#[Ctrl(label: 'name')]
interface EventWithGiftsInterface
{
    public function getGifts(): ObjectStorage;
}
