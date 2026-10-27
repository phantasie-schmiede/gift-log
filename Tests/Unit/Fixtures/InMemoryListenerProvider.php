<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\GiftLog\Tests\Unit\Fixtures;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Class InMemoryListenerProvider
 *
 * PSR-14 listener provider with listeners registered at runtime, for use with the core EventDispatcher in unit tests.
 *
 * @package PSBits\GiftLog\Tests\Unit\Fixtures
 */
class InMemoryListenerProvider implements ListenerProviderInterface
{
    /** @var array<class-string, list<callable>> */
    private array $listeners = [];

    public function addListener(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function getListenersForEvent(object $event): iterable
    {
        $eventClasses = array_merge(
            [get_class($event)],
            array_values(class_parents($event)),
            array_values(class_implements($event))
        );

        foreach ($eventClasses as $eventClass) {
            foreach ($this->listeners[$eventClass] ?? [] as $listener) {
                yield $listener;
            }
        }
    }
}
