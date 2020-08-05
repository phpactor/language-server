Testing
=======

This package includes some tools to make testing easier.

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

Unit Testing Handlers, Services etc.
------------------------------------

You can use the :ref:`LanguageServerTester` to test your handlers, services,
commands etc as follows:

.. code-block:: php

    <?php

    $tester = LanguageServerTesterBuilder::create()
        ->addHanlder($myHandler)
        ->addServiceProvider($myServiceProvider)
        ->addCommand($myCommand)
        ->build();

    $result = $tester->requestAndWait('soMyThing', []);

Lean more about the :ref:`LanguageServerTester <LanguageServerTester>`

Integration Testing
-------------------

If you are using the ``LanguageServerBuilder`` to manage the instantiation of
your ``LanguageServer`` then, assuming you are using some kind of dependency
injection container, you can use the ``tester`` method to get the
:ref:`LanguageServerTester`.

.. code-block:: php

    <?php

    $builder = $container->get(LanguageServerBuilder::class);
    assert($builder instanceof LanguageServerBuilder);
    $tester = $builder->tester();
    $response = $tester->requestAndWait('foobar', ['bar' => 'foo']);
    $response = $tester->notifyAndWait('foobar', ['bar' => 'foo']);

This will provide the :ref:`LanguageServerTester` with the "real" dispatcher.

.. _LanguageServerTester:

Language Server Tester
----------------------

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
