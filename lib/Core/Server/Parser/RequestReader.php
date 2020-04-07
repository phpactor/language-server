<?php

namespace Phpactor\LanguageServer\Core\Server\Parser;

use Amp\ByteStream\InputStream;
use Amp\Promise;
use Closure;
use Phpactor\LanguageServer\Core\Rpc\Request;
use function json_encode;
use Phpactor\LanguageServer\Core\Server\Parser\Exception\CouldNotDecodeBody;
use Phpactor\LanguageServer\Core\Server\Parser\Exception\CouldNotParseHeader;

final class RequestReader implements StreamParser
{
    const EVENT_REQUEST_READY = 'request.ready';
    const HEADER_CONTENT_LENGTH = 'Content-Length';

    /**
     * @var string[]
     */
    private $buffer = [];

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
            while (null !== $chunk = yield $this->stream->read()) {
                for ($i = 0; $i < strlen($chunk); $i++) {
                    $this->buffer[] = $chunk[$i];

                    // start by parsing the headers:
                    if ($this->headers === null && array_slice($this->buffer, -4, 4) === [
                        "\r", "\n",
                        "\r", "\n"
                    ]) {
                        $this->headers = $this->parseHeaders(
                            implode('', array_slice($this->buffer, 0, -4))
                        );
                        $this->buffer = [];
                        continue;
                    }

                    if (null === $this->headers) {
                        continue;
                    }

                    // we finished parsing the headers, now parse the body
                    if (!isset($this->headers[self::HEADER_CONTENT_LENGTH])) {
                        throw new CouldNotParseHeader(sprintf(
                            'Header did not contain mandatory Content-Length in "%s"',
                            json_encode($this->headers)
                        ));
                    }

                    $contentLength = (int) $this->headers[self::HEADER_CONTENT_LENGTH];
                    if (count($this->buffer) !== $contentLength) {
                        continue;
                    }
                }
            }

            $request = new Request($this->headers, $this->decodeBody($this->buffer));
            $this->buffer = [];
            $this->headers = null;

            return $request;
        });
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

    private function decodeBody(array $chars): array
    {
        $string = implode('', $chars);
        $array = json_decode($string, true);

        if (null === $array) {
            throw new CouldNotDecodeBody(sprintf(
                'Could not decode "%s": %s', $string, json_last_error_msg()
            ));
        }

        return $array;
    }
}
