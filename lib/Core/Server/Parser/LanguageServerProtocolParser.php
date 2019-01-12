<?php

namespace Phpactor\LanguageServer\Core\Server\Parser;

use Closure;
use Phpactor\LanguageServer\Core\Rpc\Request;
use function json_encode;
use Phpactor\LanguageServer\Core\Server\Parser\Exception\CouldNotDecodeBody;
use Phpactor\LanguageServer\Core\Server\Parser\Exception\CouldNotParseHeader;

final class LanguageServerProtocolParser implements StreamParser
{
    const EVENT_REQUEST_READY = 'request.ready';
    const HEADER_CONTENT_LENGTH = 'Content-Length';

    private $buffer = [];

    private $headers = null;

    /**
     * @var Closure
     */
    private $handler;

    public function __construct(Closure $handler)
    {
        $this->handler = $handler;
    }

    public function feed(string $chunk): void
    {
        for ($i = 0; $i < strlen($chunk); $i++) {

            // start by parsing the headers:
            $this->buffer[] = $chunk[$i];

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

            if (count($this->buffer) === $contentLength) {
                $request = new Request($this->headers, $this->decodeBody($this->buffer));
                $this->buffer = [];
                $this->headers = null;

                $handler = $this->handler;
                $handler($request);
            }
        }
    }

    private function parseHeaders(string $rawHeaders)
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
            throw new CouldNotDecodeBody(json_last_error_msg());
        }

        return $array;
    }
}
