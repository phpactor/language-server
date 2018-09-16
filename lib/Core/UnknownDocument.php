<?php

namespace Phpactor\LanguageServer\Core;

use Exception;

class UnknownDocument extends Exception
{
    public function __construct($documentUri)
    {
        parent::__construct(sprintf(
            'Unknown text document "%s"',
            $documentUri
        ));
    }
}
