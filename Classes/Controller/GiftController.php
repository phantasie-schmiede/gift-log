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
use PSB\PsbContacts\Domain\Model\Contact;
use PSB\PsbContacts\Domain\Model\Event as ContactEvent;
use PSB\PsbContacts\Domain\Repository\ContactRepository;
use PSB\PsbContacts\Domain\Repository\EventRepository as ContactEventRepository;
use PSB\PsbContacts\Service\ContactService;
use PSB\PsbFoundation\Attribute\PluginAction;
use PSB\PsbFoundation\Exceptions\MisconfiguredTcaException;
use PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use PSB\PsbFoundation\Service\UploadService;
use PSB\PsbFoundation\Traits\ControllerCacheTrait;
use PSB\PsbGiftLog\Domain\Model\Event as GiftLogEvent;
use PSB\PsbGiftLog\Domain\Model\Gift;
use PSB\PsbGiftLog\Domain\Model\Relation\EventGift;
use PSB\PsbGiftLog\Domain\Repository\EventRepository as GiftLogEventRepository;
use PSB\PsbGiftLog\Domain\Repository\GiftRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use function in_array;
use function is_array;

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
        protected readonly ContactRepository      $contactRepository,
        protected readonly ContactService         $contactService,
        protected readonly ContactEventRepository $contactEventRepository,
        protected readonly GiftLogEventRepository $giftLogEventRepository,
        protected readonly GiftRepository         $giftRepository,
        protected readonly PersistenceManager     $persistenceManager,
        protected readonly UploadService          $uploadService,
    ) {
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws IllegalObjectTypeException
     * @throws JsonException
     * @throws MisconfiguredTcaException
     * @throws NoSuchCacheGroupException
     * @throws NotFoundExceptionInterface
     * @throws PropertyNotAccessibleException
     * @throws ReflectionException
     */
    #[PluginAction(uncached: true)]
    public function createAction(
        Gift         $gift,
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->processRelations($this->request->getArgument('gift'), $gift);
        $this->giftRepository->add($gift);
        $this->persistenceManager->persistAll();
        $this->updateImages($gift);
        $this->deletePagesCache(self::CACHE_TAG);

        return $this->redirect(
            actionName    : 'listForEvent',
            controllerName: 'History',
            arguments: [
                'contactEvent' => $contactEvent,
                'giftLogEvent' => $giftLogEvent,
            ]
        );
    }

    /**
     * @throws NoSuchCacheGroupException
     * @throws IllegalObjectTypeException
     */
    #[PluginAction(uncached: true)]
    public function deleteAction(
        Gift         $gift,
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->giftRepository->remove($gift);
        $this->deletePagesCache(self::CACHE_TAG);

        return $this->redirect(
            actionName    : 'listForEvent',
            controllerName: 'History',
            arguments: [
                'contactEvent' => $contactEvent,
                'giftLogEvent' => $giftLogEvent,
            ]
        );
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[PluginAction(uncached: true)]
    public function editAction(
        Gift         $gift,
        Contact      $contact = null,
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->prepareForm($contact, $contactEvent, $gift, $giftLogEvent);

        return $this->htmlResponse();
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[PluginAction(uncached: true)]
    public function newAction(
        Contact      $contact = null,
        ContactEvent $contactEvent = null,
        Gift         $gift = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->prepareForm($contact, $contactEvent, $gift, $giftLogEvent);

        return $this->htmlResponse();
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws IllegalObjectTypeException
     * @throws JsonException
     * @throws MisconfiguredTcaException
     * @throws NoSuchCacheGroupException
     * @throws NotFoundExceptionInterface
     * @throws PropertyNotAccessibleException
     * @throws ReflectionException
     * @throws UnknownObjectException
     */
    #[PluginAction(uncached: true)]
    public function updateAction(
        Gift         $gift,
        Contact      $contact = null,
        ContactEvent $contactEvent = null,
        GiftLogEvent $giftLogEvent = null,
    ): ResponseInterface {
        $this->processRelations($this->request->getArgument('gift'), $gift);
        $this->giftRepository->update($gift);
        $this->updateImages($gift);
        $this->deletePagesCache(self::CACHE_TAG);

        if ($contact instanceof Contact) {
            $actionName = 'listForContact';
            $arguments = [
                'contact' => $contact,
            ];
        } else {
            $actionName = 'listForEvent';
            $arguments = [
                'contactEvent' => $contactEvent,
                'giftLogEvent' => $giftLogEvent,
            ];
        }

        return $this->redirect(
            actionName    : $actionName,
            controllerName: 'History',
            arguments     : $arguments
        );
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function addEventToEventGroup(array &$eventGroups, ContactEvent $event, string $key): array
    {
        $eventGroups[$key][] = [
            'label' => $event->getLabel() . ' (' . $event->getContactsAsEnumeration() . ')',
            'value' => $event::class . ':' . $event->getUid(),
        ];

        return $eventGroups;
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function collectEvents(?Contact $contact, ?ContactEvent $contactEvent): array
    {
        $eventGroups = [];
        $collectedContactEventUids = [];

        if ($contact instanceof Contact) {
            foreach ($contact->getEvents() as $event) {
                $this->addEventToEventGroup($eventGroups, $event, 'related');
                $collectedContactEventUids[] = $event->getUid();
            }
        }

        if (null !== $contactEvent) {
            /** @var Contact $eventContact */
            foreach ($contactEvent->getContacts() as $eventContact) {
                /** @var ContactEvent $event */
                foreach ($eventContact->getEventsInDescendingOrder() as $event) {
                    if (in_array($event->getUid(), $collectedContactEventUids, true)) {
                        continue;
                    }

                    $this->addEventToEventGroup($eventGroups, $event, 'related');
                    $collectedContactEventUids[] = $event->getUid();
                }
            }
        }

        /** @var GiftLogEvent $event */
        foreach ($this->giftLogEventRepository->findAll() as $event) {
            $eventGroups['common'][] = [
                'label' => $event->getName(),
                'value' => $event::class . ':' . $event->getUid(),
            ];
        }

        foreach ($this->contactEventRepository->findAll() as $event) {
            if (in_array($event->getUid(), $collectedContactEventUids, true)) {
                continue;
            }

            $this->addEventToEventGroup($eventGroups, $event, 'others');
        }

        foreach ($eventGroups as &$eventGroup) {
            usort(
                $eventGroup,
                static fn(array $a, array $b) => $a['label'] <=> $b['label']
            );
        }

        return $eventGroups;
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function prepareForm(
        Contact      $contact = null,
        ContactEvent $contactEvent = null,
        Gift         $gift = null,
        GiftLogEvent $giftLogEvent = null,
    ): void {
        $allContactsDemand = new ContactDemand();

        if (null !== $gift) {
            $donors = $gift->getDonors();
            $recipients = $gift->getRecipients();

            $preselectedDonors = [];
            $preselectedRecipients = [];

            /** @var Contact $donor */
            foreach ($donors as $donor) {
                $preselectedDonors[] = [
                    'label' => $donor->getDisplayName(),
                    'value' => $donor->getUid(),
                ];
            }

            /** @var Contact $recipient */
            foreach ($recipients as $recipient) {
                $preselectedRecipients[] = [
                    'label' => $recipient->getDisplayName(),
                    'value' => $recipient->getUid(),
                ];
            }

            $this->view->assignMultiple([
                'preselectedDonors'     => json_encode($preselectedDonors, JSON_THROW_ON_ERROR),
                'preselectedRecipients' => json_encode($preselectedRecipients, JSON_THROW_ON_ERROR),
            ]);
        } elseif (null !== $contactEvent) {
            $preselectedRecipients = [];

            /** @var Contact $eventContact */
            foreach ($contactEvent->getContacts() as $eventContact) {
                $preselectedRecipients[] = [
                    'label' => $eventContact->getDisplayName(),
                    'value' => $eventContact->getUid(),
                ];
            }

            $this->view->assign('preselectedRecipients', json_encode($preselectedRecipients, JSON_THROW_ON_ERROR));
        }

        $this->view->assignMultiple([
            'allContacts'  => $this->contactService->getAsJson($allContactsDemand),
            'contact'      => $contact,
            'contactEvent' => $contactEvent,
            'eventGroups'  => $this->collectEvents($contact, $contactEvent),
            'gift'         => $gift,
            'giftLogEvent' => $giftLogEvent,
        ]);
    }

    /**
     * @throws JsonException
     */
    private function processRelations(array $arguments, Gift $gift): void
    {
        $this->updateContacts($arguments, $gift);
        $this->updateEvents($arguments, $gift);
    }

    /**
     * @throws JsonException
     */
    private function updateContacts(array $arguments, Gift $gift): void
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

    private function updateEventRelationProperties(
        EventGift $relation,
        array     $entityData,
    ): void {
        [
            $eventClass,
            $uid,
        ] = explode(':', $entityData['event']);

        if (ContactEvent::class === $eventClass) {
            $event = $this->contactEventRepository->findByUid($uid);
        } else {
            $event = $this->giftLogEventRepository->findByUid($uid);
        }

        $relation->setEvent($event);
        $relation->setYear((int)$entityData['year']);
    }

    private function updateEvents(array $arguments, Gift $gift): void
    {
        $existingEntities = clone $gift->getEventRelations();

        $updatedEntities = $this->request->hasArgument('eventGiftRelation') ? $this->request->getArgument(
            'eventGiftRelation'
        ) : [];
        $newEntities = GlobalVariableService::get(RequestParameterProvider::class . '.eventGiftRelation', false);

        if (is_array($updatedEntities)) {
            foreach ($updatedEntities as $uid => $entityData) {
                /*
                 * Preselected entities are new entities, but are delivered in the plugin namespace because they are
                 * initially rendered inside the f:form-tag. So we have to move them to the right array.
                 */
                if (isset($entityData['preselection']) && 'true' === $entityData['preselection']) {
                    $newEntities[] = $entityData;
                    continue;
                }

                $entity = $gift->getEventRelationByUid($uid);
                $this->updateEventRelationProperties($entity, $entityData);
                $existingEntities->detach($entity);
            }
        }

        if (0 < $existingEntities->count()) {
            // Detach entities that were removed.
            foreach ($existingEntities as $entity) {
                $gift->removeEventRelation($entity);
            }
        }

        if (is_array($newEntities)) {
            foreach ($newEntities as $entityData) {
                $relation = new EventGift();
                $this->updateEventRelationProperties($relation, $entityData);
                $gift->addEventRelation($relation);
            }
        }
    }

    /**
     * @throws AspectNotFoundException
     * @throws ContainerExceptionInterface
     * @throws MisconfiguredTcaException
     * @throws NotFoundExceptionInterface
     * @throws PropertyNotAccessibleException
     * @throws ReflectionException
     */
    private function updateImages(Gift $gift): void
    {
        $this->uploadService->fromRequest(
            $gift,
            $this->request
        );
    }
}
