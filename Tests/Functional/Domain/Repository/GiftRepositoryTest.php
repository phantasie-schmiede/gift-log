<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Functional\Domain\Repository;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Model\Contact;
use PSBits\ContactBook\Domain\Repository\ContactRepository;
use PSBits\GiftLog\Domain\Demand\GiftDemand;
use PSBits\GiftLog\Domain\Model\Gift;
use PSBits\GiftLog\Domain\Repository\GiftRepository;
use PSBits\Qa\Testing\Support\PersistenceSupportTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class GiftRepositoryTest
 *
 * @package PSBits\GiftLog\Tests\Functional\Domain\Repository
 */
class GiftRepositoryTest extends FunctionalTestCase
{
    use PersistenceSupportTrait;

    protected array $testExtensionsToLoad = [
        'typo3/sysext/fluid_styled_content',
        'typo3conf/ext/friendsoftypo3/content-blocks',
        'typo3conf/ext/psbits/contact-book',
        'typo3conf/ext/psbits/foundation',
        'typo3conf/ext/psbits/frontend-templates',
        'typo3conf/ext/psbits/frontend-users',
        'typo3conf/ext/psbits/gift-log',
        'typo3conf/ext/psbits/view-helpers',
        'typo3conf/ext/wapplersystems/ws-scss',
    ];

    private ContactRepository $contactRepository;
    private GiftRepository    $giftRepository;

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    private function createGiftsWithContacts(): array
    {
        $annasGift = new Gift();
        $annasGift->setDescription('Annas gift');

        $bensGift = new Gift();
        $bensGift->setDescription('Bens gift');

        $anna = new Contact();
        $anna->setFirstName('Anna');
        $anna->setLastName('Schmidt');

        $ben = new Contact();
        $ben->setFirstName('Ben');
        $ben->setLastName('Mayer');

        $annasGift->addDonor($anna);
        $annasGift->addRecipient($ben);
        $bensGift->addDonor($ben);
        $bensGift->addRecipient($ben);

        $this->contactRepository->add($anna);
        $this->contactRepository->add($ben);
        $this->giftRepository->add($annasGift);
        $this->giftRepository->add($bensGift);
        $this->persistAll();

        return [$anna, $ben];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepository = $this->get(ContactRepository::class);
        $this->giftRepository    = $this->get(GiftRepository::class);
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    #[Test]
    public function aDemandWithAContactMatchesGiftsWhereTheContactIsDonorOrRecipient(): void
    {
        [$anna] = $this->createGiftsWithContacts();

        $demand = (new GiftDemand())->setContact($anna);
        $gifts  = $this->giftRepository->findByDemand($demand);

        self::assertCount(1, $gifts);

        /** @var Gift $gift */
        $gift = $gifts->getFirst();

        self::assertSame('Annas gift', $gift->getDescription());
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    #[Test]
    public function aDemandWithoutAContactMatchesAllGifts(): void
    {
        $this->createGiftsWithContacts();

        $gifts = $this->giftRepository->findByDemand(new GiftDemand());

        self::assertCount(2, $gifts);
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    #[Test]
    public function aDemandWithAnUnrelatedContactMatchesNoGifts(): void
    {
        [, $ben] = $this->createGiftsWithContacts();

        $stranger = new Contact();
        $stranger->setFirstName('Sara');
        $stranger->setLastName('Muster');
        $this->contactRepository->add($stranger);
        $this->persistAll();

        $demand = (new GiftDemand())->setContact($stranger);
        $gifts  = $this->giftRepository->findByDemand($demand);

        self::assertCount(0, $gifts);
    }
}
