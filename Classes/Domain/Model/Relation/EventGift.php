<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Model\Relation;

use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbFoundation\Attribute\TCA\Column;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Number;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbGiftLog\Domain\Model\Event as GiftLogEvent;
use PSB\PsbGiftLog\Domain\Model\Gift;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class EventGift
 *
 * @package PSB\PsbGiftLog\Domain\Model\Relation
 */
#[Ctrl(label: 'year')]
class EventGift extends AbstractEntity
{
    #[Select(linkedModel: ContactEvent::class, prependItem: Select::EMPTY_DEFAULT_ITEM)]
    protected ?ContactEvent $contactEvent = null;

    #[Select(linkedModel: Gift::class)]
    protected ?Gift $gift = null;

    #[Select(linkedModel: GiftLogEvent::class, prependItem: Select::EMPTY_DEFAULT_ITEM)]
    protected ?GiftLogEvent $giftLogEvent = null;

    #[Column(nullable: true)]
    #[Number]
    protected ?int $year = null;

    public function getEvent(): ContactEvent|GiftLogEvent
    {
        return $this->contactEvent ?? $this->giftLogEvent;
    }

    public function getGift(): ?Gift
    {
        return $this->gift;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setEvent(ContactEvent|GiftLogEvent $event): self
    {
        if ($event instanceof ContactEvent) {
            $this->contactEvent = $event;
            $this->giftLogEvent = null;
        } else {
            $this->contactEvent = null;
            $this->giftLogEvent = $event;
        }

        return $this;
    }

    public function setGift(?Gift $gift): self
    {
        $this->gift = $gift;

        return $this;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }
}
