<?php

namespace Phpactor\LanguageServer\Example\Command;

use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;

class SayHelloCommand implements Command
{
    public function __construct(private ClientApi $api)
    {
    }

    public function __invoke(string $name): void
    {
        $this->api->window()->showMessage()->info(sprintf('Hello %s!', $name));
    }
}
