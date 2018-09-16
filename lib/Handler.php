<?php

namespace Phpactor\LanguageServer;

interface Handler
{
    public function name(): string;

    public function handle(array $params);
}
