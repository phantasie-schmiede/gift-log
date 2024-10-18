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
use PSB\PsbGiftLog\Domain\Model\Event;
use PSB\PsbGiftLog\Domain\Model\PsbContacts\Event as ContactEvent;
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

    #[PluginAction(default: true)]
    public function listForEventAction(ContactEvent $contactEvent = null, Event $event = null): ResponseInterface
    {
        $this->view->assign('event', $event ?? $contactEvent);

        return $this->htmlResponse();
    }
}
