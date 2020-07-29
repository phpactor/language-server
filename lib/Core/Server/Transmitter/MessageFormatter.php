<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;

interface MessageFormatter
{
    public function format(Message $message): string;
}
