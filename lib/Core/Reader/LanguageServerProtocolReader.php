<?php

namespace Phpactor\LanguageServer\Core\Reader;

use Phpactor\LanguageServer\Core\Exception\ResetConnection;
use Phpactor\LanguageServer\Core\Exception\ServerError;
use Phpactor\LanguageServer\Core\IO;
use Phpactor\LanguageServer\Core\Reader;
use Phpactor\LanguageServer\Core\Transport\Request;
use Psr\Log\LoggerInterface;

class LanguageServerProtocolReader implements Reader
{
    const HEADER_CONTENT_LENGTH = 'Content-Length';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function readRequest(IO $io): Request
    {
        $headers = $this->readHeaders($io);
        $length = $this->getLengthFromHeaders($headers);

        $body = $io->read($length);

        if (false === $body->hasContents()) {
            throw new ResetConnection('No contents read from stream');
        }

        return new Request($headers, $body->contents());
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

    private function readHeaders(IO $io)
    {
            $rawHeaders = [];
            $buffer = [];
            
            while (true) {
                $chunk = $io->read(1);
        
                if (!$chunk->hasContents()) {
                    throw new ResetConnection('Input did not return anything');
                }
        
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
        
            $this->logger->debug('headers', $headers);
        return $headers;
    }

    private function getLengthFromHeaders($headers)
    {
        if (!array_key_exists(self::HEADER_CONTENT_LENGTH, $headers)) {
            throw new ServerError(sprintf(
                'No valid Content-Length header provided in raw headers: "%s"',
                implode(', ', array_keys($headers))
            ));
        }
        
        $length = (int) $headers[self::HEADER_CONTENT_LENGTH];
        
        if ($length < 1) {
            throw new ServerError(sprintf(
                'Content length must be greater than 0, got: %s',
                $length
            ));
        }
        return $length;
    }
}
