<?php

namespace Phpactor\LanguageServer\Core\Serializer;

use Phpactor\LanguageServer\Core\Deserializer;
use Phpactor\LanguageServer\Core\Exception\ServerError;
use Phpactor\LanguageServer\Core\Serializer;

class JsonSerializer implements Serializer, Deserializer
{
    public function serialize(array $payload): string
    {
        $string = json_encode($payload);

        return $string;
    }

    public function deserialize(string $payload): array
    {
        $array = json_decode($payload, true);

        if (json_last_error()) {
            throw new ServerError(sprintf(
                'Could not decode JSON string "%s", got error "%s"', $payload, json_last_error_msg()
            ));
        }

        return $array;
    }
}
