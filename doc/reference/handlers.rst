Method Handlers
===============

Method handlers handle the RPC calls from the client. 

They look like this:

.. code-block:: php

    <?php

    use Phpactor\LanguageServer\Core\Handler\Handler;
    use Amp\Promise;

    class MyHandler implements Handler
    {
        public function methods(): array
        {
            return [
                'method/name' => 'doSomething',
            ];
        }

        public function doSomething($args, CancellationToken $canellation): Promise
        {
            return new Success('hello!');
        }
    }

Once registered this command will respond to an RPC request to ``method/name``
with ``hello!``.

Argument Resolvers
------------------

The first arguments passed to the parameter will depend on the argument resolvers
used by the ``HandlerRunner``, the last argument is *always* a cancellation
token more on this later.

.. code-block:: php

    <?php

    $runner = new HandlerMethodRunner(
        new Handlers(new MyHandler()),
        new ChainArgumentResolver(
            new LanguageSeverProtocolParamsResolver(),
            new PassThroughArgumentResolver()
        ),
    );

Here we use the ``ChainArgumentResolver`` to try two different stragies.

LanguageServerProtocolParamsResolver
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This strategy will see if your method implements an LSP ``*Params`` class and
automatically instantaite it for you:

.. code-block:: php
    
    <?php

    class MyHandler implements Handler
    {
        public function methods(): array
        {
            return [
                'textDocument/completion' => 'complete',
            ];
        }

        public function doSomething(CompletionParams $completionParams, CancellationToken $canellation): Promise
        {
            $uriToTextDocument = $completionParams->textDocument->uri;
            // ...
        }
    }

You should be able to do this with *any method documented in the language
server specification*.

DTLArgumentResolver
~~~~~~~~~~~~~~~~~~~

This argument resolver will try and match the parameters from the request to
the parameters of your method.

PassThroughArgumentResolver
~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is a fallback resolver which will simply pass the raw array of arguments.

Co-routines
-----------

Your method MUST return an ``Amp\Promise``. If you return immediately you can
use the ``new Success($value)`` promise, if you do any *interruptable** work
which takes a significant amount of time you should use a co-routing. For
example:

.. code-block:: php
    
    <?php

    class MyHandler implements Handler
    {
        //...

        public function doSomething(CompletionParams $params, CancellationToken $canellation): Promise
        {
            return \Amp\call(function () {
                // ...
                $completionItems = [];

                foreach($this->magicCompletionProvider->provideCompletions($params) as $completion) {
                    $completionItems[] = $completion;
                    yield Amp\delay(1);
                }

                return $completionItems;
            });

        }
    }

The above will process a single completion item but then yield control back to
the server for 1 millisecond before continuing. This allows the server to do
other things (like for example **cancel this request**).

Cancellation
------------

The ``CancellationToken`` passed to the method handler can throw an exception
if the request is cancelled as follows:

.. code-block:: php
    
    <?php

    class MyHandler implements Handler
    {
        //...

        public function doSomething(CompletionParams $params, CancellationToken $canellation): Promise
        {
            return \Amp\call(function () {
                // ...
                $completionItems = [];

                foreach($this->magicCompletionProvider->provideCompletions($params) as $completion) {
                    $completionItems[] = $completion;
                    yield Amp\delay(1);
                    try {
                        $cancellation->throwIfRequested();
                    } catch (Amp\CancelledException $cancelled) {
                        break;
                    }
                }

                return $completionItems;
            });
        }
    }

In the above example, when the server cancels this request, the exception will
be thrown and we will return early.
