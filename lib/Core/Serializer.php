<?php

namespace Phpactor\LanguageServer\Core;

interface Serializer
{
    public function serialize(array $payload): string;
}
