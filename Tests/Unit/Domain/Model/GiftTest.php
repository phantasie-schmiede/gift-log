<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\Domain\Model;

use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Model\Contact;
use PSBits\GiftLog\Domain\Model\Gift;
use PSBits\GiftLog\Domain\Model\Relation\EventGift;
use PSBits\Qa\Testing\Support\LanguageStackSeeder;
use ReflectionProperty;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

use function strrpos;
use function substr;

class GiftTest extends UnitTestCase
{
    use LanguageStackSeeder;

    protected bool $resetSingletonInstances = true;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * The donor and recipient lists are rendered through the extension language file, which the core language
         * stack cannot load in unit tests. The seeder pre-seeds a language service factory that serves the
         * enumeration words instead.
         */
        $labels = static function(string $key): string {
            $separatorPosition = strrpos($key, ':');

            if (false !== $separatorPosition) {
                $key = substr($key, $separatorPosition + 1);
            }

            return match ($key) {
                'separator'   => ', ',
                'conjunction' => 'and',
                default       => $key,
            };
        };
        $this->seedLanguageStack($labels);
    }

    private static function entityWithUid(int $uid): Contact
    {
        $contact = new Contact();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($contact, $uid);

        return $contact;
    }

    #[Test]
    public function allContactsCanBeRemovedAtOnce(): void
    {
        $gift = (new Gift())
            ->addDonor(self::entityWithUid(1))
            ->addRecipient(self::entityWithUid(2));

        $gift->removeAllContacts();

        self::assertCount(0, $gift->getDonors());
        self::assertCount(0, $gift->getRecipients());
    }

    #[Test]
    public function anEmptyRecipientListYieldsAnEmptyJsonArray(): void
    {
        self::assertSame('[]', (new Gift())->getRecipientUidsAsJson());
    }

    #[Test]
    public function anEventRelationIsReturnedByItsUid(): void
    {
        $relation = new EventGift();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($relation, 42);
        $gift = new Gift();

        $gift->addEventRelation($relation);

        self::assertSame($relation, $gift->getEventRelationByUid(42));
    }

    #[Test]
    public function aNewGiftHasNoRelations(): void
    {
        $gift = new Gift();

        self::assertCount(0, $gift->getDonors());
        self::assertCount(0, $gift->getEventRelations());
        self::assertCount(0, $gift->getImages());
        self::assertCount(0, $gift->getRecipients());
    }

    #[Test]
    public function anUnknownEventRelationUidThrowsAnException(): void
    {
        $gift = new Gift();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1731501874);

        $gift->getEventRelationByUid(42);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function aSingleDonorIsListedByHisShortName(): void
    {
        $donor = new Contact();
        $donor->setFirstName('Anna');
        $donor->setLastName('Schmidt');
        $gift = (new Gift())->addDonor($donor);

        self::assertSame('Anna Schmidt', $gift->getDonorList());
    }

    #[Test]
    public function contactsCanBeAddedAsDonorsAndRecipients(): void
    {
        $donor     = self::entityWithUid(1);
        $recipient = self::entityWithUid(2);
        $gift      = (new Gift())
            ->addDonor($donor)
            ->addRecipient($recipient);

        self::assertSame([$donor], $gift->getDonors()->toArray());
        self::assertSame([$recipient], $gift->getRecipients()->toArray());
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function multipleRecipientsOfDifferentFamiliesAreListedAsEnumeration(): void
    {
        $first = new Contact();
        $first->setFirstName('Anna');
        $first->setLastName('Schmidt');

        $second = new Contact();
        $second->setFirstName('Ludwig');
        $second->setLastName('Mayer');

        $gift = (new Gift())
            ->addRecipient($first)
            ->addRecipient($second);

        self::assertSame('Anna Schmidt and Ludwig Mayer', $gift->getRecipientList());
    }

    #[Test]
    public function theDonorUidsAreReturnedAsJsonArray(): void
    {
        $gift = (new Gift())
            ->addDonor(self::entityWithUid(1))
            ->addDonor(self::entityWithUid(2));

        self::assertSame('[1,2]', $gift->getDonorUidsAsJson());
    }
}
