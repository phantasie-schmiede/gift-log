<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\EventListener;

use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PSBits\ContactBook\Domain\Model\Event as ContactEvent;
use PSBits\ContactBook\Domain\Repository\EventRepository as ContactEventRepository;
use PSBits\ContactBook\Event\AdditionalEventListContentEvent;
use PSBits\GiftLog\Data\ExtensionInformation;
use PSBits\GiftLog\Domain\Demand\EventGiftDemand;
use PSBits\GiftLog\Domain\Repository\Relation\EventGiftRepository;
use PSBits\GiftLog\EventListener\AdditionalEventListContent;
use PSBits\GiftLog\Tests\Unit\Fixtures\InMemoryListenerProvider;
use PSBits\FrontendTemplates\Service\ViewService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionProperty;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class AdditionalEventListContentTest
 *
 * Dispatches the additional content event through the core PSR-14 dispatcher, with the repositories and the view
 * service stubbed.
 *
 * @package PSBits\GiftLog\Tests\Unit\EventListener
 */
class AdditionalEventListContentTest extends UnitTestCase
{
    #[Test]
    public function theRenderedViewIsAppendedToTheEventContent(): void
    {
        $contactEvent  = new ContactEvent();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($contactEvent, 5);
        $extendedEvent = new ContactEvent();

        $request = new ServerRequest('http://example.com/', 'GET');
        $event   = (new AdditionalEventListContentEvent())
            ->setParameters(['event' => $contactEvent])
            ->setRequest($request);

        $contactEventRepository = $this->createMock(ContactEventRepository::class);
        $contactEventRepository
            ->method('findByUid')
            ->with(5)
            ->willReturn($extendedEvent);

        $demand = null;

        $queryResult = $this->createMock(QueryResultInterface::class);
        $queryResult
            ->method('count')
            ->willReturn(2);

        $eventGiftRepository = $this->createMock(EventGiftRepository::class);
        $eventGiftRepository
            ->method('findByDemand')
            ->willReturnCallback(
                static function (EventGiftDemand $foundDemand) use (&$demand, $queryResult): QueryResultInterface {
                    $demand = $foundDemand;

                    return $queryResult;
                }
            );

        $view = $this->createMock(ViewInterface::class);
        $view
            ->method('assignMultiple')
            ->willReturnSelf();
        $view
            ->method('render')
            ->willReturn('<div class="gifts"></div>');

        $viewService = $this->createMock(ViewService::class);
        $viewService
            ->method('createStandaloneView')
            ->with(self::anything(), $request, 'Event/AdditionalEventListContent')
            ->willReturn($view);

        $extensionInformation = $this->createMock(ExtensionInformation::class);

        $listener = new AdditionalEventListContent(
            $contactEventRepository,
            $extensionInformation,
            $eventGiftRepository,
            $viewService
        );

        $provider = new InMemoryListenerProvider();
        $provider->addListener(AdditionalEventListContentEvent::class, $listener);

        (new EventDispatcher($provider))->dispatch($event);

        self::assertSame('<div class="gifts"></div>', $event->getContent());
        self::assertSame($contactEvent, $demand->getContactEvent());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    public function hasGiftsReflectsTheAmountOfRelations(): void
    {
        $contactEvent = new ContactEvent();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($contactEvent, 5);

        $event = (new AdditionalEventListContentEvent())
            ->setParameters(['event' => $contactEvent])
            ->setRequest(new ServerRequest('http://example.com/', 'GET'));

        $contactEventRepository = $this->createMock(ContactEventRepository::class);
        $contactEventRepository
            ->method('findByUid')
            ->willReturn(new ContactEvent());

        $queryResult = $this->createMock(QueryResultInterface::class);
        $queryResult
            ->method('count')
            ->willReturn(0);

        $eventGiftRepository = $this->createMock(EventGiftRepository::class);
        $eventGiftRepository
            ->method('findByDemand')
            ->willReturn($queryResult);

        $view = $this->createMock(ViewInterface::class);

        $assigned = null;

        $view
            ->method('assignMultiple')
            ->willReturnCallback(
                static function (array $variables) use (&$assigned, $view): ViewInterface {
                    $assigned = $variables;

                    return $view;
                }
            );
        $view
            ->method('render')
            ->willReturn('');

        $viewService = $this->createMock(ViewService::class);
        $viewService
            ->method('createStandaloneView')
            ->willReturn($view);

        $listener = new AdditionalEventListContent(
            $contactEventRepository,
            $this->createMock(ExtensionInformation::class),
            $eventGiftRepository,
            $viewService
        );

        $provider = new InMemoryListenerProvider();
        $provider->addListener(AdditionalEventListContentEvent::class, $listener);

        (new EventDispatcher($provider))->dispatch($event);

        self::assertSame('', $event->getContent());
        self::assertInstanceOf(ContactEvent::class, $assigned['event']);
        self::assertFalse($assigned['hasGifts']);
    }
}
