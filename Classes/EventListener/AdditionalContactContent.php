<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\EventListener;

use PSB\PsbContacts\Event\AdditionalContactContentEvent;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbGiftLog\Data\ExtensionInformation;
use PSB\PsbGiftLog\Domain\Demand\GiftDemand;
use PSB\PsbGiftLog\Domain\Repository\GiftRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class AdditionalContactContent
 *
 * @package PSB\PsbGiftLog\EventListener
 */
#[AsEventListener(identifier: 'PsbGiftLog/AdditionalContactContent')]
readonly class AdditionalContactContent
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
    public function __invoke(AdditionalContactContentEvent $event): void
    {
        $viewFactoryData = new ViewFactoryData(
               templatePathAndFilename: FilePathUtility::getPrivateResourcePath(
                $this->extensionInformation,
                'Templates/Event/AdditionalContactContent.html'
            ), request                : $event->getRequest(),
        );
        $view = $this->viewFactory->create($viewFactoryData);

        $demand = (new GiftDemand())->setContact($event->getContact());
        $view->assignMultiple([
            'contact' => $event->getContact(),
            'gifts'   => $this->giftRepository->findByDemand($demand),
        ]);

        $event->addContent($view->render());
    }
}
