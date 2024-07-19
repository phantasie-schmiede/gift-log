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
 * Class GiftDemand
 *
 * @package PSB\PsbGiftLog\Domain\Demand
 */
class GiftDemand
{
    protected ?Contact $recipient = null;

    public function getRecipient(): ?Contact
    {
        return $this->recipient;
    }

    public function setRecipient(?Contact $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }
}
