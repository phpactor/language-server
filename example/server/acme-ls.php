#!/usr/bin/env php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use AcmeLs\AcmeLsDispatcherFactory;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Psr\Log\NullLogger;

$logger = new NullLogger();
LanguageServerBuilder::create(new AcmeLsDispatcherFactory($logger))
    ->build()
    ->run();
