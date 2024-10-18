<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Demand;

use PSB\PsbContacts\Domain\Model\Contact;
use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbGiftLog\Domain\Model\Event;

/**
 * Class GiftDemand
 *
 * @package PSB\PsbGiftLog\Domain\Demand
 */
class GiftDemand
{
    protected ?Contact                $contact = null;
    protected ContactEvent|Event|null $event   = null;

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function getEvent(): Event|ContactEvent|null
    {
        return $this->event;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function setEvent(Event|ContactEvent|null $event): self
    {
        $this->event = $event;

        return $this;
    }
}
