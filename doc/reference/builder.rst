Language Server Builder
=======================

The language server builder takes care of:

- Creating the necessary streams.
- Creating the :ref:`tester <LanguageServerTester>`.

It is optional, you can also have a look inside and instantiate the server
yourself.

It accepts:

- ``Phpactor\LanguageServer\Core\Dispatcher\DispatcherFactory``.
- An optional PSR ``Psr\Log\LoggerInterface``.

.. code-block:: php

    <?php

    use Phpactor\LanguageServer\LanguageServerBuilder;

    $server = Phpactor\LanguageServer\LanguageServerBuilder::create(
         new MyDispatcher(),
         new NullLogger()
    )->build();

    $server->run();
    // or
    $promise = $server->start();

Run or Start
------------

The `run` method on the built language server will start the server and listen for connections. It will also register an error and signal handler.

The `start` method will simply return a promise, without doing anything extra.
