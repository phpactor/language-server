<?php

namespace Phpactor\LanguageServer\Core\Reader;

use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Reader;
use Phpactor\LanguageServer\Core\Transport\Request;

class PlaybackReader implements Reader
{
    public function readRequest(IO $io): Request
    {
        $body = '';
        while ($byte = $io->read(1)) {
            $body .= $byte;
        }

        return new Request([
            'Content-Length' => strlen($body),
        ], $body);
    }
}
