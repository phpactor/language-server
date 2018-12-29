Phpactor Language Server
========================

[![Build Status](https://travis-ci.org/phpactor/language-server.svg?branch=master)](https://travis-ci.org/phpactor/language-server)

This package provides a language server infrastructure without any language
server functionality:

- Asynchronous TCP server.
- Manages text document synchronization.
- Allows you to define server capabilities and supply handlers.

Example
-------

Create a dumb language server with no functionality:

```php
$server = LanguageServerBuilder::create()
    ->tcpServer()
    ->useDefaultHandlers()
    ->build();

$server->start();
```

In order to support language server features we create Handlers, for example:

```php
<?php

namespace Phpactor\LanguageServer\Example;

use LanguageServerProtocol\CompletionItem;
use LanguageServerProtocol\CompletionList;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Dispatcher\Handler;
use Phpactor\LanguageServer\Core\Session\SessionManager;

class ExampleCompletionHandler implements Handler
{
    private $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function methods(): array
    {
        return [
            'textDocument/completion' => 'complete'
        ];
    }

    public function complete(TextDocumentItem $textDocument, Position $position): CompletionList
    {
        $textDocument = $this->sessionManager->current()->workspace()->get($textDocument->uri);
        $completionList = new CompletionList();

        // ... do whatever we need to do to get the completion information

        $completionList->items[] = new CompletionItem('foobar');
        $completionList->items[] = new CompletionItem('foofoo');

        yield $completionList;
    }
}
```

Which can then be registered with the server, for example with the builder:

```php
$sessionManager = new SessionManager();
$server = LanguageServerBuilder::create($sessionManager)
    ->tcpServer()
    ->useDefaultHandlers()
    ->addHandler(new MyCompletionHandler($sessionManager))
    ->build();

$server->start();
```
