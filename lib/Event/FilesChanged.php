<?php

namespace Phpactor\LanguageServer\Event;

use Phpactor\LanguageServerProtocol\FileEvent;

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
}
