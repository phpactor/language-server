<?php

namespace Phpactor\LanguageServer\Core\Reader;

use Phpactor\LanguageServer\Core\Exception\ServerError;
use Phpactor\LanguageServer\Core\IO;
use Psr\Log\LoggerInterface;

class LanguageServerProtocolReader
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function readRequest(IO $io)
    {
        $rawHeaders = [];
        $buffer = [];
        
        while (true) {
            $chunk = $io->read(1);
            $buffer[] = $chunk->contents();

            if (count($buffer) >= 2 && array_slice($buffer, -2, 2) == [ "\r", "\n" ]) {
                $header = trim(implode('', array_slice($buffer, 0, -2)));
    
                if (!$header) {
                    break;
                }
    
                $buffer = [];
                $rawHeaders[] = $header;
            }
        }

        $headers = $this->parseHeaders($rawHeaders);

        if (!array_key_exists('Content-Length', $headers)) {
            throw new ServerError(sprintf(
                'No valid Content-Length header provided in raw headers: "%s"',
                implode(', ', $rawHeaders)
            ));
        }

        $length = (int) $headers['Content-Length'];
        if ($length < 1) {
            throw new ServerError(sprintf(
                'Content length must be greater than 0, got: %s', $length
            ));
        }

        $body = $io->read($length);

        if (false === $body->hasContents()) {
            throw new ServerError('No contents read from stream');
        }

        return [ $headers, $body->contents() ];
    }

    private function parseHeaders(array $rawHeaders): array
    {
        $parsed = [];
        foreach ($rawHeaders as $rawHeader) {
            $keyValue = explode(':', $rawHeader);
            if (count($keyValue) != 2) {
                $this->logger->warning(sprintf('Invalid header "%s"', $rawHeader));
                continue;
            }

            $parsed[$keyValue[0]] = trim($keyValue[1]);
        }

        return $parsed;
    }

}
