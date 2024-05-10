<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace PSB\PsbGiftLog\Domain\Model;

use PSB\PsbBudgetManager\Utility\ItemUtility;
use PSB\PsbContacts\Domain\Model\Contact;
use PSB\PsbContacts\Domain\Model\Event;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Inline;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Input;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbFoundation\Enum\SelectRenderType;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class Entry
 *
 * @package PSB\PsbGiftLog\Domain\Model
 */
#[Ctrl(label: 'name')]
class Entry extends AbstractEntity
{
    #[Select(linkedModel: Event::class)]
    protected Event $event;

    #[Select(linkedModel: Contact::class, mm: '', renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $donors;
}
