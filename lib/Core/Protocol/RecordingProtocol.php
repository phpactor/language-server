<?php

namespace Phpactor\LanguageServer\Core\Protocol;

use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Protocol;
use Phpactor\LanguageServer\Core\Transport\Request;
use RuntimeException;

class RecordingProtocol implements Protocol
{
    /**
     * @var Protocol
     */
    private $innerProtocol;

    /**
     * @var resource
     */
    private $recordStream;

    public function __construct(Protocol $innerProtocol, string $recordPath)
    {
        $this->innerProtocol = $innerProtocol;
        $stream = fopen($recordPath, 'w');

        if (false === $stream) {
            throw new RuntimeException(sprintf(
                'Could not open stream "%s"',
                $recordPath
            ));
        }

        $this->recordStream = $stream;
    }

    public function readRequest(IO $io): Request
    {
        $request = $this->innerProtocol->readRequest($io);
        fwrite($this->recordStream, implode("\r\n", array_map(function ($key, $value) {
            return $key .':'.$value;
        }, array_keys($request->headers()), array_values($request->headers()))) . "\r\n\r\n");
        fwrite($this->recordStream, $request->body());
        return $request;
    }

    public function __destruct()
    {
        fclose($this->recordStream);
    }

    public function writeResponse(IO $io, $response): void
    {
        $this->innerProtocol->writeResponse($io, $response);
    }
}
