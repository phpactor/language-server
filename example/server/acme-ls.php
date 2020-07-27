#!/usr/bin/env php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use AcmeLs\AcmeLsDispatcherFactory;
use Phpactor\LanguageServer\LanguageServerBuilder;

$builder = LanguageServerBuilder::create(new AcmeLsDispatcherFactory())
    ->build()
    ->run();
