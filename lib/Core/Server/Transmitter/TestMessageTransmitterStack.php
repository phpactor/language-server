<?php

namespace Phpactor\LanguageServer\Core\Server\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;

interface TestMessageTransmitterStack
{
    public function shift(): ?Message;

    public function clear(): void;
}
