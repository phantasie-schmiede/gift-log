<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Domain\Demand;

use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Domain\Model\Gift;

/**
 * Class EventGiftDemand
 *
 * @package PSBits\GiftLog\Domain\Demand
 */
class EventGiftDemand
{
    protected ?ContactEvent $contactEvent = null;
    protected ?GiftLogEvent $giftLogEvent = null;

    /** @var list<Gift> */
    protected array $gifts = [];

    public function getContactEvent(): ?ContactEvent
    {
        return $this->contactEvent;
    }

    public function getGiftLogEvent(): ?GiftLogEvent
    {
        return $this->giftLogEvent;
    }

    /**
     * @return list<Gift>
     */
    public function getGifts(): array
    {
        return $this->gifts;
    }

    public function setContactEvent(ContactEvent $contactEvent): self
    {
        $this->contactEvent = $contactEvent;

        return $this;
    }

    public function setEvent(GiftLogEvent|ContactEvent $param): void
    {
        if ($param instanceof ContactEvent) {
            $this->setContactEvent($param);
        } else {
            $this->setGiftLogEvent($param);
        }
    }

    public function setGiftLogEvent(GiftLogEvent $giftLogEvent): self
    {
        $this->giftLogEvent = $giftLogEvent;

        return $this;
    }

    /**
     * @param list<Gift> $gifts
     */
    public function setGifts(array $gifts): self
    {
        $this->gifts = $gifts;

        return $this;
    }
}
