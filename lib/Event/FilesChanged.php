<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\FileChangeType;
use Phpactor\LanguageServerProtocol\FileEvent;
use RuntimeException;
use function array_shift;

final class FilesChanged
{
    /**
     * @var array
     */
    private $events;

    public function __construct(FileEvent ...$events)
    {
        $this->events = $events;
    }

    public function events(): array
    {
        return $this->events;
    }

    public function byType(int $type): self
    {
        return new self(...array_filter($this->events, function (FileEvent $event) use ($type) {
            return $event->type === $type;
        }));
    }

    public function first(): FileEvent
    {
        $first = reset($this->events);

        if (null === $first) {
            throw new RuntimeException(
                'No file events, cannot get the first one',
            );
        }

        return $first;
    }
}