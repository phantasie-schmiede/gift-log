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
use PSBits\ContactBook\Domain\Model\Contact;
use PSBits\ContactBook\Event\ModifyTabsEvent;
use PSBits\GiftLog\Data\ExtensionInformation;
use PSBits\GiftLog\Domain\Demand\GiftDemand;
use PSBits\GiftLog\Domain\Repository\GiftRepository;
use PSBits\GiftLog\EventListener\ModifyTabs;
use PSBits\GiftLog\Tests\Unit\Fixtures\FakeLanguageServiceFactory;
use PSBits\GiftLog\Tests\Unit\Fixtures\InMemoryListenerProvider;
use PSBits\FrontendTemplates\Service\ViewService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionProperty;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ModifyTabsTest
 *
 * Dispatches the modify tabs event through the core PSR-14 dispatcher, with the repository and the view service
 * stubbed. The icon factory and the language stack are pre-seeded singletons.
 *
 * @package PSBits\GiftLog\Tests\Unit\EventListener
 */
class ModifyTabsTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * The tab icon is rendered through the icon factory and the extension language file, neither of which is
         * available in unit tests. Pre-seeded singletons serve a stubbed icon and the tab label instead.
         */
        $languageService = $this->createMock(LanguageService::class);
        $languageService->method('getLocale')
            ->willReturn(new Locale('en'));
        $languageService->method('sL')
            ->willReturn('Gifts');

        GeneralUtility::setSingletonInstance(
            LanguageServiceFactory::class,
            new FakeLanguageServiceFactory($languageService)
        );

        $cache = $this->createMock(FrontendInterface::class);
        $cache->method('get')
            ->willReturn($languageService);

        $cacheManager = $this->createMock(CacheManager::class);
        $cacheManager->method('getCache')
            ->willReturn($cache);

        GeneralUtility::setSingletonInstance(CacheManager::class, $cacheManager);

        $icon = $this->createMock(Icon::class);
        $icon->method('render')
            ->willReturn('<svg></svg>');

        $iconFactory = $this->createMock(IconFactory::class);
        $iconFactory->method('getIcon')
            ->with('templates-gift')
            ->willReturn($icon);

        GeneralUtility::addInstance(IconFactory::class, $iconFactory);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    public function theGiftLogTabIsPrependedToTheTabs(): void
    {
        $contact = new Contact();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($contact, 3);

        $request = new ServerRequest('http://example.com/', 'GET');
        $event   = new ModifyTabsEvent($contact, $request, [
            ['content' => '<existing-tab/>', 'id' => 'existing', 'label' => 'Existing'],
        ]);

        $gifts = $this->createMock(QueryResultInterface::class);

        $giftRepository = $this->createMock(GiftRepository::class);
        $giftRepository
            ->method('findByDemand')
            ->willReturnCallback(
                static function (GiftDemand $demand) use ($contact, $gifts): QueryResultInterface {
                    self::assertSame($contact, $demand->getContact());

                    return $gifts;
                }
            );

        $view = $this->createMock(ViewInterface::class);
        $view
            ->method('assignMultiple')
            ->with(['contact' => $contact, 'gifts' => $gifts])
            ->willReturnSelf();
        $view
            ->method('render')
            ->willReturn('<ul class="gifts"></ul>');

        $viewService = $this->createMock(ViewService::class);
        $viewService
            ->method('createStandaloneView')
            ->with(self::anything(), $request, 'Event/AdditionalContactContent')
            ->willReturn($view);

        $extensionInformation = $this->createMock(ExtensionInformation::class);
        $extensionInformation
            ->method('getExtensionKey')
            ->willReturn('gift_log');

        $listener = new ModifyTabs(
            $extensionInformation,
            $giftRepository,
            $viewService
        );

        $provider = new InMemoryListenerProvider();
        $provider->addListener(ModifyTabsEvent::class, $listener);

        (new EventDispatcher($provider))->dispatch($event);

        $tabs = $event->getTabs();

        self::assertCount(2, $tabs);
        self::assertSame('gift-log', $tabs[0]['id']);
        self::assertSame('<svg></svg>', $tabs[0]['label']);
        self::assertSame('<ul class="gifts"></ul>', $tabs[0]['content']);
        self::assertSame('existing', $tabs[1]['id']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    public function theGiftLogTabIsAppendedWithoutExistingTabs(): void
    {
        $contact = new Contact();
        (new ReflectionProperty(AbstractDomainObject::class, 'uid'))->setValue($contact, 4);

        $event = new ModifyTabsEvent($contact, new ServerRequest('http://example.com/', 'GET'));

        $gifts = $this->createMock(QueryResultInterface::class);
        $gifts
            ->method('count')
            ->willReturn(0);

        $giftRepository = $this->createMock(GiftRepository::class);
        $giftRepository
            ->method('findByDemand')
            ->willReturn($gifts);

        $view = $this->createMock(ViewInterface::class);
        $view
            ->method('assignMultiple')
            ->willReturnSelf();
        $view
            ->method('render')
            ->willReturn('');

        $viewService = $this->createMock(ViewService::class);
        $viewService
            ->method('createStandaloneView')
            ->willReturn($view);

        $listener = new ModifyTabs(
            $this->createMock(ExtensionInformation::class),
            $giftRepository,
            $viewService
        );

        $provider = new InMemoryListenerProvider();
        $provider->addListener(ModifyTabsEvent::class, $listener);

        (new EventDispatcher($provider))->dispatch($event);

        $tabs = $event->getTabs();

        self::assertCount(1, $tabs);
        self::assertSame('gift-log', $tabs[0]['id']);
        self::assertSame('', $tabs[0]['content']);
    }
}
