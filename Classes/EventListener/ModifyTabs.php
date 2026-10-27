<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\EventListener;

use JsonException;
use PSBits\ContactBook\Event\ModifyTabsEvent;
use PSBits\Foundation\Utility\Configuration\FilePathUtility;
use PSBits\Foundation\Utility\LocalizationUtility;
use PSBits\FrontendTemplates\Service\ViewService;
use PSBits\GiftLog\Data\ExtensionInformation;
use PSBits\GiftLog\Domain\Demand\GiftDemand;
use PSBits\GiftLog\Domain\Repository\GiftRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class ModifyTabs
 *
 * @package PSBits\GiftLog\EventListener
 */
#[AsEventListener(identifier: 'GiftLog/ModifyTabs')]
readonly class ModifyTabs
{
    public function __construct(
        protected ExtensionInformation $extensionInformation,
        protected GiftRepository       $giftRepository,
        protected ViewService          $viewService,
    ) {
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws InvalidQueryException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ModifyTabsEvent $event): void
    {
        $view = $this->viewService->createStandaloneView(
            $this->extensionInformation,
            $event->getRequest(),
            'Event/AdditionalContactContent'
        );

        $demand = (new GiftDemand())->setContact($event->getContact());
        $view->assignMultiple([
            'contact' => $event->getContact(),
            'gifts'   => $this->giftRepository->findByDemand($demand),
        ]);

        $event->addTab($view->render(), 'gift-log', $this->getTabIcon(), -1);
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function getTabIcon(): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icon        = $iconFactory->getIcon('templates-gift');
        $icon->setTitle(
            LocalizationUtility::translate(
                FilePathUtility::getLanguageFilePath(
                    $this->extensionInformation
                ) . 'Frontend/Contact/show.xlf:gifts'
            )
        );

        return $icon->render();
    }
}
