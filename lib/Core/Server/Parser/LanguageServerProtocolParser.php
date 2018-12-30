<?php

namespace Phpactor\LanguageServer\Core\Server\Parser;

use Generator;
use Phpactor\LanguageServer\Core\Rpc\Request;
use function json_encode;

final class LanguageServerProtocolParser
{
    const EVENT_REQUEST_READY = 'request.ready';
    const HEADER_CONTENT_LENGTH = 'Content-Length';

    private $buffer = [];

    public function __invoke(): Generator
    {
        $headers = null;

        $chunk = yield;

        while (true) {
            for ($i = 0; $i < strlen($chunk); $i++) {

                // start by parsing the headers:
                $this->buffer[] = $chunk[$i];

                if ($headers === null && array_slice($this->buffer, -4, 4) === [
                    "\r", "\n",
                    "\r", "\n"
                ]) {
                    $headers = $this->parseHeaders(implode('', array_slice($this->buffer, 0, -4)));
                    $this->buffer = [];
                    continue;
                }

                if (null === $headers) {
                    continue;
                }

                // we finished parsing the headers, now parse the body

                if (!isset($headers[self::HEADER_CONTENT_LENGTH])) {
                    throw new CouldNotParseHeader(sprintf(
                        'Header did not contain mandatory Content-Length in "%s"',
                        json_encode($headers)
                    ));
                }

                $contentLength = (int) $headers[self::HEADER_CONTENT_LENGTH];

                if (count($this->buffer) === $contentLength) {
                    $request = new Request($headers, $this->decodeBody($this->buffer));
                    $this->buffer = [];
                    $headers = null;
                    $chunk .= (string) yield $request;
                }
            }

            $chunk = yield null;
        }

        yield null;
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
