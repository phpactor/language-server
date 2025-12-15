<?php

namespace Phpactor\LanguageServer\Core\Server\Client;

use Phpactor\LanguageServer\Core\Server\RpcClient;

final class DiagnosticsClient
{
    public function __construct(private RpcClient $client)
    {
    }

    public function publishDiagnostics(string $uri, ?int $version, array $diagnostics): void
    {
        $this->client->notification('textDocument/publishDiagnostics', [
            'uri' => $uri,
            'version' => $version,
            'diagnostics' => $diagnostics,
        ]);
    }
}
