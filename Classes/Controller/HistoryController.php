<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Controller;

use PSB\PsbFoundation\Attribute\PluginAction;
use PSB\PsbFoundation\Traits\ControllerCacheTrait;
use PSB\PsbGiftLog\Domain\Demand\GiftDemand;
use PSB\PsbGiftLog\Domain\Model\Event;
use PSB\PsbGiftLog\Domain\Model\PsbContacts\Event as ContactEvent;
use PSB\PsbGiftLog\Domain\Repository\GiftRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * Class HistoryController
 *
 * @package PSB\PsbGiftLog\Controller
 */
class HistoryController extends ActionController
{
    use ControllerCacheTrait;

    public const string CACHE_TAG = 'tx_psbgiftlog';

    public function __construct(
        protected GiftRepository $giftRepository,
    ) {
    }

    /**
     * @throws InvalidQueryException
     */
    #[PluginAction(default: true)]
    public function listForEventAction(ContactEvent $contactEvent = null, Event $event = null): ResponseInterface
    {
        $demand = (new GiftDemand())->setEvent($event ?? $contactEvent);

        $this->view->assignMultiple([
            'event' => $event ?? $contactEvent,
            'gifts' => $this->giftRepository->findByDemand($demand),
        ]);

        return $this->htmlResponse();
    }
}
