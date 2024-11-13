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

/**
 * Class EventGiftDemand
 *
 * @package PSB\PsbGiftLog\Domain\Demand
 */
class GiftDemand
{
    protected ?Contact $contact = null;

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }
}
