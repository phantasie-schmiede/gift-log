<?php
declare(strict_types=1);

/*
 * This file is part of PSB Gift Log.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbGiftLog\ViewHelpers;

use PSB\PsbGiftLog\Service\EventService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class EventIdentifierViewHelper
 *
 * @package PSB\PsbGiftLog\ViewHelpers
 */
class EventIdentifierViewHelper extends AbstractViewHelper
{
    public function __construct(
        protected EventService $eventService,
    ) {
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'event',
            'object',
            'an event record',
            true
        );
    }

    public function render(): string
    {
        return $this->eventService->getUniqueIdentifier($this->arguments['event']);
    }
}
