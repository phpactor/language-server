Phpactor Language Server
========================

[![Build Status](https://travis-ci.org/phpactor/language-server.svg?branch=master)](https://travis-ci.org/phpactor/language-server)

This package provides a language server infrastructure without any language
server functionality:

- Serves as either STDIO or TCP.
- Manages files.
- Allows you to define server capabilities and supply handlers.

Example
-------

Create a dumb language server with no functionality:

```php
$server = LanguageServerBuilder::create()
    ->tcpServer()
    ->coreHandlers()
    ->build();

$server->start();
```

In order to support language server features we create Handlers, for example:

```php
<?php

use LanguageServerProtocol\CompletionItem;
use LanguageServerProtocol\CompletionList;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\SessionManager;

class MyCompletorHandler implements Handler
{
    private $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function name(): string
    {
        return 'textDocuemnt/completion';
    }

    public function handle(TextDocumentItem $textDocument, Position $position): CompletionList
    {
        $textDocument = $this->sessionManager->current()->workspace()->get($textDocuemnt->uri);
        $completionList = new CompletionList();

        // ... do whatever we need to do to get the completion information

        $completionList->items[] = new CompletionItem('foobar');
        $completionList->items[] = new CompletionItem('foofoo');

        return $completionList;
    }
}
```

Which can then be registered with the server, for example with the builder:

```php
$sessionManager = new SessionManager();
$server = LanguageServerBuilder::create($sessionManager)
    ->tcpServer()
    ->coreHandlers()
    ->addHandler(new MyCompletionHandler($sessionManager))
    ->build();

$server->start();
```
