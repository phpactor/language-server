Testing
=======

One of the aims of this package was to try and make it relatively easy to test
the language server as a whole and with individual components.

Protocol Factory
----------------

The ``ProtocolFactory`` is a utility class for creating LSP protocol objects:

.. code-block:: php

    <?php

    use Phpactor\LanguageServer\Test\ProtocolFactory;

    $item = ProtocolFactory::textDocumentItem('uri', 'content');
    $initializeParams = ProtocolFactory::initializeParams('/path/to/rootUri');

This is useful as the LSP objects can be complicated and we can assume some
defaults using the factory.

Handler Testing
---------------

The ``HandlerTester`` is a wrapper to make unit testing handlers easier:

.. code-block:: php

    <?php

    $tester = new HandlerTester(new ExitHandler());
    $result = $tester->requestAndWait('exit', []);

Language Server Tester
----------------------

The ``LanguageServerTester`` is for integration testing.

Below we assume that you have the ``LanguageServerBuilder`` in your 
dependency injection container, we can get the tester as follows:

.. code-block:: php

    <?php

    $builder = $container->get(LanguageServerBuilder::class);
    assert($builder instanceof LanguageServerBuilder);
    $tester = $builder->tester();
    $response = $tester->requestAndWait('foobar', ['bar' => 'foo']);
    $response = $tester->notifyAndWait('foobar', ['bar' => 'foo']);

The tester provides access to a test transmitter from which you can access any
message sent by the server:

.. code-block:: php

    <?php

    // ...
    $messageOrNull = $tester->transmitter()->shift();

You can also use some convenience methods to control the server:

.. code-block:: php

    <?php

    // ...
    $messageOrNull = $tester->textDocument()->open('/uri/to/text.php', 'content');
    $tester->services()->start('myservice');

The tester will automatically initialize the server, but you can also pass
your own initialization parameters:

.. code-block:: php

    <?php

    // ...
    $tester = $builder->tester(ProtocolFactory::initializeParams('/uri/foobar.php'));
