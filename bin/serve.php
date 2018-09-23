#!/usr/bin/env php
<?php

use Phpactor\LanguageServer\Adapter\DTL\DTLArgumentResolver;
use Phpactor\LanguageServer\Core\Connection\TcpServerConnection;
use Phpactor\LanguageServer\Core\IO\StreamIO;
use Phpactor\LanguageServer\Core\ChunkIO\TcpIO;
use Phpactor\LanguageServer\Core\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\LanguageServer\Core\Dispatcher\MethodDispatcher;
use Phpactor\LanguageServer\Core\Handlers;
use Phpactor\LanguageServer\Core\Server;
use Psr\Log\AbstractLogger;


require __DIR__ . '/../vendor/autoload.php';

$in = fopen('php://stdin', 'r');
$out = fopen('php://stdout', 'w');

$logger = new class extends AbstractLogger {
    private $err;
    public function __construct()
    {
        $this->err = fopen('php://stderr', 'w');
    }

    public function log($level, $message, array $context = [])
    {
        fwrite($this->err, sprintf('[%s] %s %s', $level, $message, json_encode($context) . PHP_EOL));
    }
};

$factory = new TcpServerConnection($logger, '127.0.0.1:8888');
$resolver = new DTLArgumentResolver();
$handlers = new Handlers([]);
$dispatcher = new ErrorCatchingDispatcher(new MethodDispatcher($resolver, $handlers));
$server = new Server($logger, $dispatcher, $factory);
$server->start();
