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
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getEvents(): array
    {
        return GeneralUtility::makeInstance(ObjectService::class)
            ->resolveMmRelationWithDifferentTables($this, 'events');
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
