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
use PSB\PsbFoundation\Attribute\TCA\ColumnType\File;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Number;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Text;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbFoundation\Enum\SelectRenderType;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
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
    #[Text]
    protected string $description = '';

    /** @var ObjectStorage<Contact> */
    #[Select(linkedModel: Contact::class, mm: 'tx_psbgiftlog_entry_contact_donor', renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $donors;

    /** @var ObjectStorage<Event> */
    #[Select(linkedModel: Event::class, mm: 'tx_psbgiftlog_entry_event', renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $events;

    /** @var ObjectStorage<FileReference> */
    #[File]
    protected ObjectStorage $images;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDonors(): ObjectStorage
    {
        return $this->donors;
    }

    public function getEvents(): ObjectStorage
    {
        return $this->events;
    }

    public function getImages(): ObjectStorage
    {
        return $this->images;
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
        $this->events = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->recipients = new ObjectStorage();
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setDonors(ObjectStorage $donors): void
    {
        $this->donors = $donors;
    }

    public function setEvents(ObjectStorage $events): void
    {
        $this->events = $events;
    }

    public function setImages(ObjectStorage $images): void
    {
        $this->images = $images;
    }

    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    public function setRecipients(ObjectStorage $recipients): void
    {
        $this->recipients = $recipients;
    }

    public function setRedeemed(?bool $redeemed): void
    {
        $this->redeemed = $redeemed;
    }

    public function setYear(?int $year): void
    {
        $this->year = $year;
    }
}
