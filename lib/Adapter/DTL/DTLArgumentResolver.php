<?php

namespace Phpactor\LanguageServer\Adapter\DTL;

use DTL\ArgumentResolver\ArgumentResolver as UpstreamArgumentResolver;
use DTL\ArgumentResolver\ParamConverter\RecursiveInstantiator;
use Phpactor\LanguageServer\Core\Dispatcher\ArgumentResolver;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;

final class DTLArgumentResolver implements ArgumentResolver
{
    public function __construct(private UpstreamArgumentResolver $dtlArgumnetResolver = new UpstreamArgumentResolver(
        [new RecursiveInstantiator()],
        UpstreamArgumentResolver::ALLOW_UNKNOWN_ARGUMENTS | UpstreamArgumentResolver::MATCH_TYPE
    ))
    {
    }

    public function resolveArguments(object $object, string $method, Message $message): array
    {
        if (!$message instanceof RequestMessage && !$message instanceof NotificationMessage) {
            return [];
        }

        return $this->dtlArgumnetResolver->resolveArguments($object::class, $method, $message->params ?? []);
    }
}
