<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\Controller;

use JsonException;
use PSB\PsbContacts\Domain\Demand\ContactDemand;
use PSB\PsbContacts\Domain\Repository\ContactRepository;
use PSB\PsbContacts\Service\ContactService;
use PSB\PsbFoundation\Attribute\PluginAction;
use PSB\PsbFoundation\Traits\ControllerCacheTrait;
use PSB\PsbGiftLog\Domain\Model\Event as GiftLogEvent;
use PSB\PsbGiftLog\Domain\Model\Gift;
use PSB\PsbGiftLog\Domain\Model\PsbContacts\Event as ContactEvent;
use PSB\PsbGiftLog\Domain\Repository\GiftRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class GiftController
 *
 * @package PSB\PsbGiftLog\Controller
 */
class GiftController extends ActionController
{
    use ControllerCacheTrait;

    public const string CACHE_TAG = 'tx_psbgiftlog';

    public function __construct(
        protected ContactRepository $contactRepository,
        protected ContactService    $contactService,
        protected GiftRepository    $giftRepository,
    ) {
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NoSuchCacheGroupException
     */
    #[PluginAction(uncached: true)]
    public function createAction(
        Gift         $gift,
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        die('Not ready yet!');
        $this->processRelations($this->request->getArgument('gift'), $gift);
        $this->giftRepository->add($gift);
        $this->deletePagesCache(self::CACHE_TAG);

        if (null !== $contactEvent) {
            $arguments = ['contactEvent' => $contactEvent];
        } else {
            $arguments = ['giftLogEvent' => $giftLogEvent];
        }

        return $this->redirect('listForEvent', 'History', null, $arguments);
    }

    /**
     * @throws NoSuchCacheGroupException
     * @throws IllegalObjectTypeException
     */
    #[PluginAction(uncached: true)]
    public function deleteAction(Gift $gift): ResponseInterface
    {
        $this->giftRepository->remove($gift);
        $this->deletePagesCache(self::CACHE_TAG);

        return $this->redirect('list');
    }

    /**
     * @throws JsonException
     */
    #[PluginAction(uncached: true)]
    public function editAction(
        Gift         $gift,
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->prepareForm($contactEvent, $gift, $giftLogEvent);

        return $this->htmlResponse();
    }

    /**
     * @throws JsonException
     */
    #[PluginAction(uncached: true)]
    public function newAction(
        ContactEvent $contactEvent = null,
        Gift         $gift = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->prepareForm($contactEvent, $gift, $giftLogEvent);

        return $this->htmlResponse();
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws NoSuchCacheGroupException
     * @throws UnknownObjectException
     */
    #[PluginAction(uncached: true)]
    public function updateAction(Gift $gift): ResponseInterface
    {
        $this->giftRepository->update($gift);
        $this->deletePagesCache(self::CACHE_TAG);

        return $this->redirect(
            actionName: 'show', arguments: ['gift' => $gift]
        );
    }

    /**
     * @throws JsonException
     */
    private function prepareForm(
        ContactEvent $contactEvent = null,
        Gift         $gift = null,
        GiftLogEvent $giftLogEvent = null,
    ): void {
        $allContactsDemand = new ContactDemand();

        $this->view->assignMultiple([
            'allContacts'  => $this->contactService->getAsJson($allContactsDemand),
            'contactEvent' => $contactEvent,
            'gift'         => $gift,
            'giftLogEvent' => $giftLogEvent,
        ]);
    }

    /**
     * @throws JsonException
     */
    private function processRelations(array $arguments, Gift $gift): void
    {
        $gift->removeAllContacts();

        foreach ([
                     'donors',
                     'recipients',
                 ] as $relation) {
            $contactIds = json_decode($arguments[$relation], true, 512, JSON_THROW_ON_ERROR);
            $contactIds = array_map('\intval', $contactIds);

            foreach ($contactIds as $contactId) {
                $contact = $this->contactRepository->findByUid($contactId);

                if (null !== $contact) {
                    if ('donors' === $relation) {
                        $gift->addDonor($contact);
                    } else {
                        $gift->addRecipient($contact);
                    }
                }
            }
        }
    }
}
