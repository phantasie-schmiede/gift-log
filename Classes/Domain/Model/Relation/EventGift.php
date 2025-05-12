<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Model\Relation;

use JsonException;
use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbFoundation\Attribute\TCA\Column;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Number;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbGiftLog\Domain\Model\Event as GiftLogEvent;
use PSB\PsbGiftLog\Domain\Model\Gift;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class EventGift
 *
 * @package PSB\PsbGiftLog\Domain\Model\Relation
 */
#[Ctrl(defaultSortBy: 'year DESC', label: 'year', labelAlt: [
    'contactEvent',
    'giftLogEvent',
    'gift',
],     labelAltForce: true)]
class EventGift extends AbstractEntity
{
    #[Select(foreignTableWhere: 'ORDER BY year DESC, month DESC, day DESC', linkedModel: ContactEvent::class, prependItem: Select::EMPTY_DEFAULT_ITEM)]
    protected ?ContactEvent $contactEvent = null;

    #[Select(linkedModel: Gift::class)]
    protected ?Gift $gift = null;

    #[Select(linkedModel: GiftLogEvent::class, prependItem: Select::EMPTY_DEFAULT_ITEM)]
    protected ?GiftLogEvent $giftLogEvent = null;

    #[Column(nullable: true)]
    #[Number]
    protected ?int $year = null;

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getCompactValue(): string
    {
        return '(' . $this->year . ') ' . ($this->contactEvent ? $this->contactEvent->getLabel(
                $this->year
            ) : $this->giftLogEvent->getName());
    }

    public function getContactEvent(): ?ContactEvent
    {
        return $this->contactEvent;
    }

    public function getEvent(): ContactEvent|GiftLogEvent
    {
        return $this->contactEvent ?? $this->giftLogEvent;
    }

    public function getGift(): ?Gift
    {
        return $this->gift;
    }

    public function getGiftLogEvent(): ?GiftLogEvent
    {
        return $this->giftLogEvent;
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
