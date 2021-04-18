<?php

namespace Phpactor\LanguageServer\Test\ListenerProvider;

use Psr\EventDispatcher\ListenerProviderInterface;
use RuntimeException;

class RecordingListenerProvider implements ListenerProviderInterface
{
    /**
     * @var object[]
     */
    private $recieved = [];

    /**
     * {@inheritDoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        return [
            function (object $event): void {
                $this->recieved[] = $event;
            }
        ];
    }

    /**
     * @param string $type
     */
    public function shift(string $type): object
    {
        $next = array_shift($this->recieved);

        if (null === $next) {
            throw new RuntimeException('No more events');
        }

        if (!$next instanceof $type) {
            throw new RuntimeException(sprintf(
                'Expected event of type "%s" but got "%s"',
                $type,
                get_class($next)
            ));
        }

        return $next;
    }
}
