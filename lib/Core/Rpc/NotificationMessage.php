<?php

namespace Phpactor\LanguageServer\Core\Rpc;

final class NotificationMessage extends Message
{
    public function __construct(
        public string $method,
        /** @var array<string,mixed>|null */
        public ?array $params = null
    ) {
    }
}
