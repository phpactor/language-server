Phpactor Language Server
========================

[![Build Status](https://travis-ci.org/phpactor/language-server.svg?branch=master)](https://travis-ci.org/phpactor/language-server)

This package provides a [language
server](https://microsoft.github.io/language-server-protocol/specification) platform:

- Language server platform upon which you can implement language server
  features.
- Can run as either a TCP server (accepting many connections) or a STDIO
  server (invoked by the client).
- Can handle multiple sessions.
- Can manage text document synchronization.

See the [Language Server
Specification](https://microsoft.github.io/language-server-protocol/specification)
for a list of methods which you can implement with this package.

Example
-------

Create a dumb language server which can respond to the `initialize` command
and nothing more:

```php
$server = LanguageServerBuilder::create()
    ->tcpServer()
    ->build();

$server->start();
```

Add TextDocument Handling
-------------------------

We provide a handler for text document synchronization:

```php
$server = LanguageServerBuilder::create()
    ->addSystemHandler(new TextDocumentHandler(new Workspace()));
    ->build();

$server->start();
```

The text document handler will keep track of the files the client is managing.
The workspace will contain these files, and can be used by other handlers.

Creating Custom Handlers
------------------------

Handlers simply declare a map of Language Server RPC metods to instance
methods:

```
class MyCompletionHandler implements Handler
{
    public function methods(): array
    {
        return [
            'textDocument/completion' => 'completion',
        ];
    }

    public function completion(): Generator
    {
         $list = new CompletionList();
         $list->items[] = new CompletionItem('hello');
         $list->items[] = new CompletionItem('goodbye');

         yield $list;
    }
}
```

Create Per-Session Handlers
---------------------------

Above we created a global text document handler, which isn't great as it means
multiple clients share the same workspace.

You can use `HandlerLoader` implementations to lazily create handlers each
time a client connects to the server. It is passed the initialization
parmeters supplied by the client (which includes the root path of the clients
project):

```
class MyHandlerLoader
{
    public function load(InitializeParams $params): Handlers
    {
        $workspace = new Workspace();

        return new Handlers([
            new MyHandler($workspace)
            new MyOtherHandler($workspace)
        ]);
    }
}
```


