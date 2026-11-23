<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Domain\Model\Relation;

use InvalidArgumentException;
use JsonException;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\Foundation\Attribute\TCA\Column;
use PSBits\Foundation\Attribute\TCA\ColumnType\Number;
use PSBits\Foundation\Attribute\TCA\ColumnType\Select;
use PSBits\Foundation\Attribute\TCA\Ctrl;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Domain\Model\Gift;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

#[Ctrl(defaultSortBy: 'year DESC', label: 'year', labelAlt: [
    'contactEvent',
    'giftLogEvent',
    'gift',
], labelAltForce: true)]
class EventGift extends AbstractEntity
{
    #[Select(foreignTableWhere: 'ORDER BY year DESC, month DESC, day DESC', linkedModel: ContactEvent::class, prependItem: Select::EMPTY_DEFAULT_ITEM_OPTIONAL_INT)]
    protected ?ContactEvent $contactEvent = null;

    #[Select(linkedModel: Gift::class)]
    protected ?Gift $gift = null;

    #[Select(linkedModel: GiftLogEvent::class, prependItem: Select::EMPTY_DEFAULT_ITEM_OPTIONAL_INT)]
    protected ?GiftLogEvent $giftLogEvent = null;

    #[Column(nullable: true)]
    #[Number]
    protected ?int $year = null;

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function getCompactValue(): string
    {
        if (null !== $this->contactEvent) {
            return '(' . $this->year . ') ' . $this->contactEvent->getLabel($this->year);
        }

        if (null !== $this->giftLogEvent) {
            return '(' . $this->year . ') ' . $this->giftLogEvent->getName();
        }

        return '(' . $this->year . ')';
    }

    public function getContactEvent(): ?ContactEvent
    {
        return $this->contactEvent;
    }

    public function getEvent(): ContactEvent|GiftLogEvent|null
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
