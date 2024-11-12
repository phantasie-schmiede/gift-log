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
use PSB\PsbGiftLog\Domain\Model\Event as GiftLogEvent;
use PSB\PsbGiftLog\Domain\Model\PsbContacts\Event as ContactEvent;
use PSB\PsbGiftLog\Domain\Repository\GiftRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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

    #[PluginAction(default: true)]
    public function listForEventAction(
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->setCacheTags(self::CACHE_TAG);

        $this->view->assignMultiple([
            'contactEvent' => $contactEvent,
            'event'        => $giftLogEvent ?? $contactEvent,
            'giftLogEvent' => $giftLogEvent,
        ]);

        return $this->htmlResponse();
    }
}
