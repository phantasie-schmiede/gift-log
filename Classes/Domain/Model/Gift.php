<?php

declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Domain\Model;

use InvalidArgumentException;
use JsonException;
use PSBits\ContactBook\Domain\Model\Contact;
use PSBits\ContactBook\Utility\ContactUtility;
use PSBits\ContactBook\Utility\JsonUtility;
use PSBits\Foundation\Attribute\TCA\Column;
use PSBits\Foundation\Attribute\TCA\ColumnType\Check;
use PSBits\Foundation\Attribute\TCA\ColumnType\File;
use PSBits\Foundation\Attribute\TCA\ColumnType\Inline;
use PSBits\Foundation\Attribute\TCA\ColumnType\Select;
use PSBits\Foundation\Attribute\TCA\ColumnType\Text;
use PSBits\Foundation\Attribute\TCA\Ctrl;
use PSBits\Foundation\Enum\SelectRenderType;
use PSBits\GiftLog\Domain\Model\Relation\EventGift;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Gift
 *
 * @package PSBits\GiftLog\Domain\Model
 */
#[Ctrl(label: 'description')]
class Gift extends AbstractEntity
{
    public const array MM_TABLES = [
        'donors'     => 'tx_giftlog_gift_contact_donor_mm',
        'recipients' => 'tx_giftlog_gift_contact_recipient_mm',
    ];

    #[Text]
    protected string $description = '';

    /** @var ObjectStorage<Contact> */
    #[Select(linkedModel: Contact::class, minItems: 1, mm: self::MM_TABLES['donors'], renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $donors;

    /** @var ObjectStorage<EventGift> */
    #[Cascade(['value' => 'remove'])]
    #[Inline(foreignDefaultSortBy: 'year', foreignField: 'gift', linkedModel: EventGift::class)]
    protected ObjectStorage $eventRelations;

    /** @var ObjectStorage<FileReference> */
    #[File(allowed                   : [
        'common-image-types',
    ], uploadDuplicationBehaviour: DuplicationBehavior::RENAME, uploadFileNameGeneratorPartSeparator: '_', uploadFileNameGeneratorProperties: ['uid'], uploadTargetFolder: 'user_upload/gift_log/images', )]
    protected ObjectStorage $images;

    #[Text]
    protected string $notes = '';

    /** @var ObjectStorage<Contact> */
    #[Select(linkedModel: Contact::class, minItems: 1, mm: self::MM_TABLES['recipients'], renderType: SelectRenderType::selectMultipleSideBySide)]
    protected ObjectStorage $recipients;

    #[Column(nullable: true)]
    #[Check]
    protected ?bool $redeemed = null;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function addDonor(Contact $contact): self
    {
        $this->donors->attach($contact);

        return $this;
    }

    public function addEventRelation(EventGift $eventRelation): void
    {
        $this->eventRelations->attach($eventRelation);
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

    /**
     * @return string
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getDonorList(): string
    {
        return ContactUtility::createEnumeration($this->donors->toArray());
    }

    /**
     * @throws JsonException
     */
    public function getDonorUidsAsJson(): false|string
    {
        return JsonUtility::getUidsAsJsonArray(...$this->donors);
    }

    /** @return ObjectStorage<Contact> */
    public function getDonors(): ObjectStorage
    {
        return $this->donors;
    }

    public function getEventRelationByUid(int $uid): EventGift
    {
        foreach ($this->eventRelations as $eventRelation) {
            if ($eventRelation->getUid() === $uid) {
                return $eventRelation;
            }
        }

        throw new InvalidArgumentException(
            __CLASS__ . ': Event relation with UID ' . $uid . ' was not found!',
            1731501874
        );
    }

    /** @return ObjectStorage<EventGift> */
    public function getEventRelations(): ObjectStorage
    {
        return $this->eventRelations;
    }

    /** @return ObjectStorage<FileReference> */
    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @return string
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getRecipientList(): string
    {
        return ContactUtility::createEnumeration($this->recipients->toArray());
    }

    /**
     * @throws JsonException
     */
    public function getRecipientUidsAsJson(): false|string
    {
        return JsonUtility::getUidsAsJsonArray(...$this->recipients);
    }

    /** @return ObjectStorage<Contact> */
    public function getRecipients(): ObjectStorage
    {
        return $this->recipients;
    }

    public function getRedeemed(): ?bool
    {
        return $this->redeemed;
    }

    public function initializeObject(): void
    {
        $this->donors         = new ObjectStorage();
        $this->eventRelations = new ObjectStorage();
        $this->images         = new ObjectStorage();
        $this->recipients     = new ObjectStorage();
    }

    public function removeAllContacts(): self
    {
        $this->donors->removeAll($this->donors);
        $this->recipients->removeAll($this->recipients);

        return $this;
    }

    public function removeEventRelation(EventGift $eventRelation): self
    {
        $this->eventRelations->detach($eventRelation);

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param ObjectStorage<Contact> $donors
     */
    public function setDonors(ObjectStorage $donors): self
    {
        $this->donors = $donors;

        return $this;
    }

    /**
     * @param ObjectStorage<FileReference> $images
     */
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

    /**
     * @param ObjectStorage<Contact> $recipients
     */
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
}
