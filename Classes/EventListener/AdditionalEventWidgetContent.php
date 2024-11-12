<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\EventListener;

use PSB\PsbContacts\Event\AdditionalEventWidgetContentEvent;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbGiftLog\Data\ExtensionInformation;
use PSB\PsbGiftLog\Domain\Demand\GiftDemand;
use PSB\PsbGiftLog\Domain\Repository\GiftRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class AdditionalEventWidgetContent
 *
 * @package PSB\PsbGiftLog\EventListener
 */
#[AsEventListener(identifier: 'PsbGiftLog/AdditionalEventWidgetContent')]
readonly class AdditionalEventWidgetContent
{
    public function __construct(
        protected ExtensionInformation $extensionInformation,
        protected GiftRepository       $giftRepository,
        protected ViewFactoryInterface $viewFactory,
    ) {
    }

    /**
     * @throws InvalidQueryException
     */
    public function __invoke(AdditionalEventWidgetContentEvent $event): void
    {
        $demand = (new GiftDemand())->setEvent($event->getEvent());

        $viewFactoryData = new ViewFactoryData(
               templatePathAndFilename: FilePathUtility::getPrivateResourcePath(
                $this->extensionInformation,
                'Templates/Event/AdditionalEventWidgetContent.html'
            ), request                : $event->getRequest(),
        );
        $view = $this->viewFactory->create($viewFactoryData);

        $view->assignMultiple([
            'event'    => $event->getEvent(),
            'hasGifts' => $this->giftRepository->findByDemand($demand)
                    ?->count() > 0,
        ]);

        $event->addContent($view->render());
    }
}
