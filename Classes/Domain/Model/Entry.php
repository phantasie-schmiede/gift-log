<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Domain\Model;

use PSB\PsbContacts\Domain\Model\Contact;
use PSB\PsbContacts\Domain\Model\Event;
use PSB\PsbFoundation\Attribute\TCA\Column;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Check;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Number;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Text;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbFoundation\Enum\SelectRenderType;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Entry
 *
 * @package PSB\PsbGiftLog\Domain\Model
 */
#[Ctrl(label: 'name')]
class Entry extends AbstractEntity
{
    /** @var ObjectStorage<Contact> */
    #[Select(linkedModel: Contact::class, mm: 'tx_psbgiftlog_entry_contact_donor', renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $donors;

    #[Select(linkedModel: Event::class)]
    protected ?Event $event = null;

    #[Text]
    protected string $notes = '';

    #[Select(linkedModel: Contact::class, mm: 'tx_psbgiftlog_entry_contact_recipient', renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $recipients;

    #[Column(nullable: true)]
    #[Check]
    protected ?bool $redeemed = null;

    #[Column(nullable: true)]
    #[Number]
    protected ?int $year = null;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function getDonors(): ObjectStorage
    {
        return $this->donors;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function getRedeemed(): ?bool
    {
        return $this->redeemed;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function initializeObject(): void
    {
        $this->donors = new ObjectStorage();
        $this->recipients = new ObjectStorage();
    }
}
