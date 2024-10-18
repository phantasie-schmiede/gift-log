<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Model;

use PSB\PsbFoundation\Attribute\TCA\ColumnType\Input;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbFoundation\Enum\SelectRenderType;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Event
 *
 * @package PSB\PsbGiftLog\Domain\Model
 */
#[Ctrl(label: 'name')]
class Event extends AbstractEntity implements EventWithGiftsInterface
{
    /** @var ObjectStorage<Gift> $gifts */
    #[Select(linkedModel: Gift::class, mm: Gift::MM_TABLES['events'], mmOppositeField: 'events', renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $gifts;

    #[Input]
    protected string $name = '';

    public function __construct()
    {
        $this->initializeObject();
    }

    public function getGifts(): ObjectStorage
    {
        return $this->gifts;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function initializeObject(): void
    {
        $this->gifts = new ObjectStorage();
    }

    public function setGifts(ObjectStorage $gifts): void
    {
        $this->gifts = $gifts;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
