<?php

namespace Phpactor\LanguageServer\Core\Dispatcher;

use Phpactor\LanguageServer\Core\Dispatcher\Exception\CouldNotResolveArguments;
use Phpactor\LanguageServer\Core\Rpc\Message;

interface ArgumentResolver
{
    /**
     * @throws CouldNotResolveArguments
     */
    public function resolveArguments(object $object, string $method, Message $message): array;
}
