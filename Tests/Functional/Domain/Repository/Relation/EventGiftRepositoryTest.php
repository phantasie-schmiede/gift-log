<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Functional\Domain\Repository\Relation;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\GiftLog\Domain\Demand\EventGiftDemand;
use PSBits\GiftLog\Domain\Model\Event as GiftLogEvent;
use PSBits\GiftLog\Domain\Model\Gift;
use PSBits\GiftLog\Domain\Model\Relation\EventGift;
use PSBits\GiftLog\Domain\Repository\Relation\EventGiftRepository;
use PSBits\Qa\Testing\Support\PersistenceSupportTrait;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class EventGiftRepositoryTest
 *
 * @package PSBits\GiftLog\Tests\Functional\Domain\Repository\Relation
 */
class EventGiftRepositoryTest extends FunctionalTestCase
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

    private EventGiftRepository $eventGiftRepository;
    private Gift                $gift;
    private ?ContactEvent       $contactEvent = null;
    private ?GiftLogEvent       $giftLogEvent = null;

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    private function createRelations(): void
    {
        $this->contactEvent = new ContactEvent();
        $this->contactEvent->setLocation('Berlin');

        $this->giftLogEvent = new GiftLogEvent();
        $this->giftLogEvent->setName('Christmas');

        $this->gift = new Gift();
        $this->gift->setDescription('Test gift');

        $contactEventRelation = new EventGift();
        $contactEventRelation->setEvent($this->contactEvent);
        $contactEventRelation->setGift($this->gift);
        $contactEventRelation->setYear(2024);

        $giftLogEventRelation = new EventGift();
        $giftLogEventRelation->setEvent($this->giftLogEvent);
        $giftLogEventRelation->setGift($this->gift);
        $giftLogEventRelation->setYear(2023);

        $this->eventGiftRepository->add($contactEventRelation);
        $this->eventGiftRepository->add($giftLogEventRelation);
        $this->persistAll();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventGiftRepository = $this->get(EventGiftRepository::class);
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    #[Test]
    public function aDemandWithAContactEventMatchesOnlyItsRelations(): void
    {
        $this->createRelations();

        $demand = (new EventGiftDemand())->setContactEvent($this->contactEvent);
        $gifts  = $this->eventGiftRepository->findByDemand($demand);

        self::assertCount(1, $gifts);

        /** @var EventGift $relation */
        $relation = $gifts->getFirst();

        self::assertSame(2024, $relation->getYear());
        self::assertSame($this->contactEvent->getUid(), $relation->getContactEvent()?->getUid());
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    #[Test]
    public function aDemandWithAGiftLogEventMatchesOnlyItsRelations(): void
    {
        $this->createRelations();

        $demand = (new EventGiftDemand())->setGiftLogEvent($this->giftLogEvent);
        $gifts  = $this->eventGiftRepository->findByDemand($demand);

        self::assertCount(1, $gifts);

        /** @var EventGift $relation */
        $relation = $gifts->getFirst();

        self::assertSame(2023, $relation->getYear());
        self::assertSame($this->giftLogEvent->getUid(), $relation->getGiftLogEvent()?->getUid());
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    #[Test]
    public function aDemandWithGiftsMatchesRelationsOfAnyOfTheGifts(): void
    {
        $this->createRelations();

        $demand = (new EventGiftDemand())->setGifts([$this->gift]);
        $gifts  = $this->eventGiftRepository->findByDemand($demand);

        self::assertCount(2, $gifts);
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NotImplementedException
     */
    #[Test]
    public function aDemandWithAnUnrelatedGiftMatchesNoRelations(): void
    {
        $this->createRelations();

        $strangerGift = new Gift();
        $strangerGift->setDescription('Strangers gift');

        $demand = (new EventGiftDemand())->setGifts([$strangerGift]);
        $gifts  = $this->eventGiftRepository->findByDemand($demand);

        self::assertCount(0, $gifts);
    }
}
