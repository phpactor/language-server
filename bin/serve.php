#!/usr/bin/env php
<?php

use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Connection\StreamConnection;
use Phpactor\LanguageServer\Core\Connection\TcpServerConnection;
use Phpactor\LanguageServer\Core\Handler\Initialize;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use Phpactor\LanguageServer\Core\ChunkIO\TcpIO;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\Core\SessionManager;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Psr\Log\AbstractLogger;


require __DIR__ . '/../vendor/autoload.php';

$in = fopen('php://stdin', 'r');
$out = fopen('php://stdout', 'w');

$logger = new class extends AbstractLogger {
    private $err;
    public function __construct()
    {
        //$this->err = fopen('php://stderr', 'w');
        $this->err = fopen('phpactor-lsp.log', 'w');
    }

    public function log($level, $message, array $context = [])
    {
        fwrite($this->err, json_encode(
            [
                'level' => $level, 
                'message' => $message, 
                'context' => $context
            ]
        ).PHP_EOL);
    }
};

$server = LanguageServerBuilder::create($logger)
    ->stdIoServer()
    ->coreHandlers()
    ->build();

$server->start();
