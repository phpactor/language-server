<?php

namespace Phpactor\LanguageServer\Core\Server\Parser;

use Amp\ByteStream\InputStream;
use Amp\Promise;
use Phpactor\LanguageServer\Core\Rpc\RawMessage;
use function json_encode;
use Phpactor\LanguageServer\Core\Server\Parser\Exception\CouldNotDecodeBody;
use Phpactor\LanguageServer\Core\Server\Parser\Exception\CouldNotParseHeader;

final class LspMessageReader implements RequestReader
{
    const EVENT_REQUEST_READY = 'request.ready';
    const HEADER_CONTENT_LENGTH = 'Content-Length';

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @var string
     */
    private $overflow = '';

    /**
     * @var null|array<string>
     */
    private $headers = null;

    /**
     * @var InputStream
     */
    private $stream;

    public function __construct(InputStream $stream)
    {
        $this->stream = $stream;
    }

    public function wait(): Promise
    {
        return \Amp\call(function () {
            if (null !== $request = $this->processChunk($this->overflow)) {
                return $request;
            }

            while (null !== $chunk = yield $this->stream->read()) {
                if (null !== $request = $this->processChunk($chunk)) {
                    return $request;
                }
            }
        });
    }

    private function processChunk(string $chunk): ?RawMessage
    {
        for ($i = 0; $i < strlen($chunk); $i++) {
            $this->buffer .= $chunk[$i];

            if (null !== $request = $this->parseRequest()) {
                $this->overflow = substr($chunk, $i + 1);
                return $request;
            }
        }

        return null;
    }

    private function parseRequest(): ?RawMessage
    {
        // start by parsing the headers:
        if ($this->headers === null && substr($this->buffer, -4, 4) === "\r\n\r\n"
        ) {
            $this->headers = $this->parseHeaders(
                substr($this->buffer, 0, -4)
            );
            $this->buffer = '';
            return null;
        }

        if (null === $this->headers) {
            return null;
        }

        // we finished parsing the headers, now parse the body
        if (!isset($this->headers[self::HEADER_CONTENT_LENGTH])) {
            throw new CouldNotParseHeader(sprintf(
                'Header did not contain mandatory Content-Length in "%s"',
                json_encode($this->headers)
            ));
        }

        $contentLength = (int) $this->headers[self::HEADER_CONTENT_LENGTH];

        if (strlen($this->buffer) !== $contentLength) {
            return null;
        }

        $request = new RawMessage($this->headers, $this->decodeBody($this->buffer));
        $this->buffer = '';
        $this->headers = null;

        return $request;
    }

    private function parseHeaders(string $rawHeaders): array
    {
        $lines = explode("\r\n", $rawHeaders);
        $headers = [];

        foreach ($lines as $line) {
            [ $name, $value ] = array_map(function ($value) {
                return trim($value);
            }, explode(':', $line));
            $headers[$name] = $value;
        }

        return $headers;
    }

    private function decodeBody(string $string): array
    {
        $array = json_decode($string, true);

        if (null === $array) {
            throw new CouldNotDecodeBody(sprintf(
                'Could not decode "%s": %s',
                $string,
                json_last_error_msg()
            ));
        }

        return $array;
    }
}
