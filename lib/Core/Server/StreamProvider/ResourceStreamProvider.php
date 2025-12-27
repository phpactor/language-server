<?php

namespace Phpactor\LanguageServer\Core\Server\StreamProvider;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Server\Stream\ResourceDuplexStream;
use Psr\Log\LoggerInterface;

final class ResourceStreamProvider implements StreamProvider
{
    private bool $provided = false;

    public function __construct(private ResourceDuplexStream $duplexStream, private LoggerInterface $logger)
    {
    }

    /**
     * @return Success<null|Connection>
     */
    public function accept(): Promise
    {
        // resource connections are valid only for
        // the length of the client connnection
        if ($this->provided) {
            return new Success(null);
        }

        $this->provided = true;

        $this->logger->info('Listening on STDIO');

        return new Success(new Connection('stdio', $this->duplexStream));
    }

    public function close(): void
    {
        $this->duplexStream->close();
    }
}
