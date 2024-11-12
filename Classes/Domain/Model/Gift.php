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
use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbFoundation\Attribute\TCA\Column;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Check;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\File;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Group;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Number;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Text;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbFoundation\Enum\SelectRenderType;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Service\ObjectService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Gift
 *
 * @package PSB\PsbGiftLog\Domain\Model
 */
#[Ctrl(label: 'description')]
class Gift extends AbstractEntity
{
    public const MM_TABLES = [
        'donors'     => 'tx_psbgiftlog_gift_contact_donor_mm',
        'events'     => 'tx_psbgiftlog_gift_event_mm',
        'recipients' => 'tx_psbgiftlog_gift_contact_recipient_mm',
    ];

    #[Text]
    protected string $description = '';

    /** @var ObjectStorage<Contact> */
    #[Select(linkedModel: Contact::class, minItems: 1, mm: self::MM_TABLES['donors'], renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $donors;

    #[Group(mm: self::MM_TABLES['events'], mmOppositeUsage: [
        Event::class        => ['gifts'],
        ContactEvent::class => ['gifts'],
    ])]
    protected string $events;

    /** @var ObjectStorage<FileReference> */
    #[File]
    protected ObjectStorage $images;

    #[Text]
    protected string $notes = '';

    /** @var ObjectStorage<Contact> */
    #[Select(linkedModel: Contact::class, minItems: 1, mm: self::MM_TABLES['recipients'], renderType: SelectRenderType::selectMultipleSideBySide)]
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

    public function addDonor(Contact $contact): self
    {
        $this->donors->attach($contact);

        return $this;
    }

    public function addRecipient(Contact $contact): self
    {
        $this->recipients->attach($contact);

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDonors(): ObjectStorage
    {
        return $this->donors;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ImplementationException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getEvents(): array
    {
        return GeneralUtility::makeInstance(ObjectService::class)
            ->resolveMmRelationWithDifferentTables($this, 'events', [Event::class]);
    }

    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function getRecipients(): ObjectStorage
    {
        return $this->recipients;
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
        $this->images = new ObjectStorage();
        $this->recipients = new ObjectStorage();
    }

    public function removeAllContacts(): self
    {
        $this->donors->removeAll($this->donors);
        $this->recipients->removeAll($this->recipients);

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setDonors(ObjectStorage $donors): self
    {
        $this->donors = $donors;

        return $this;
    }

    public function setImages(ObjectStorage $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function setRecipients(ObjectStorage $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }

    public function setRedeemed(?bool $redeemed): self
    {
        $this->redeemed = $redeemed;

        return $this;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }
}
