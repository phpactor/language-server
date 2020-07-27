Commands
========

Commands are issued from the client to the server, they are similar in concept
to RPC calls with the exception that they are expcitly registered with the
server and executed via. an RPC method.

Usage
-----

The command handler accepts a ``CommandDispatcher`` which in turn accepts a
map of command *names* to invokable objects:

.. code-block:: php

    <?php

    use Phpactor\LanguageServer\Handler\Workspace\CommandHandler;
    use Phpactor\LanguageServer\Workspace\CommandDispatcher;

    // ...
    $handler = new CommandHandler(
        new CommandDispatcher([
            'my_command' => function (array $args) {
                 // do something
            }
        ])
    ));

Now, when the client connects, the server will signify (via.
``ServerCapabilties``) that this command is available.
