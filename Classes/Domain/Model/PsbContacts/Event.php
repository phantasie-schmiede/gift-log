<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Model\PsbContacts;

use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbFoundation\Attribute\TCA\Column;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\Mapping\Table;
use PSB\PsbFoundation\Attribute\TCA\Tab;
use PSB\PsbFoundation\Enum\SelectRenderType;
use PSB\PsbGiftLog\Domain\Model\EventWithGiftsInterface;
use PSB\PsbGiftLog\Domain\Model\Gift;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Event
 *
 * @package PSB\PsbGiftLog\Domain\Model\PsbContacts
 */
#[Tab(identifier: 'giftLog')]
#[Table(name: 'tx_psbcontacts_domain_model_event')]
class Event extends ContactEvent implements EventWithGiftsInterface
{
    /** @var ObjectStorage<Gift> $gifts */
    #[Column(position: 'tab:giftLog')]
    #[Select(linkedModel: Gift::class, mm: Gift::MM_TABLES['events'], mmOppositeField: 'events', renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $gifts;

    public function __construct()
    {
        parent::__construct();
        $this->initializeObject();
    }

    public function getGifts(): ObjectStorage
    {
        return $this->gifts;
    }

    public function initializeObject(): void
    {
        $this->gifts = new ObjectStorage();
    }

    public function setGifts(ObjectStorage $gifts): void
    {
        $this->gifts = $gifts;
    }
}
