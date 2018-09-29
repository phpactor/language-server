<?php

namespace Phpactor\LanguageServer\Core;

interface Deserializer
{
    public function deserialize(string $payload): array;
}
