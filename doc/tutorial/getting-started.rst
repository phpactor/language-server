Getting Started
===============

Below is an example which will run a language server which will
respond to any request with a response "Hello world!":

.. literalinclude:: ../../example/server/minimal.php
   :language: php
   :linenos:

- ``LanguageServerBuilder`` abstracts the creation of streams and
  :doc:`builds the Language Server <../reference/builder>`. It accepts an
  instance of ``DispatcherFactory`` - ``ClosureDispatcherFactory`` is a
  ``DispatcherFactory``. This
  class has the responsibility initializing the session. It is invoked when
  the Language Server client sends ``initialize`` method, providing its
  capabilities.
- ``MessageTransmitter`` is how your session can communicate with the
  client - you wouldn't normally use this directly, but more on this later.
  The ``InitializeParams`` is a class containing the initialization information
  from the client, including the ``ClientCapabilities``.
- ``MiddlewareDispatcher`` Is a ``Dispatcher`` which uses the
  Middleware concept - this is the pipeline for incoming requests. Requests go
  in, and ``ResponseMessage`` classes come out (or ``null`` if no response is
  necessary).
- ``ClosureMiddleware`` is a ``Middleware`` which allows you to
  specific a ``\Closure`` instead of implementing a new class (which is what
  you'd normally do). The ``Message`` is the incoming message
  (``Request``, ``Notification`` or ``Response``) from the client, the
  ``RequestHandler`` is used to delegate to the *next* ``Middleware``.
- We return a ``ResponseMessage`` wrapped in a ``Promise``. We only return a
  ``Response`` for ``Request`` messages, and the ``Response`` must reference
  the request's ID.
- The ``Success`` class is a ``Promise`` which resolves immediately. Returning
  a ``Promise`` allows us to run non-blocking
  co-routines_.
- Then finally build and run the server. It will listen on STDIO by default.
  

If you run this example, you should be able to connect to the language server
and it should respond (incorrectly) to all requests with "Hello World!".

Let's try it out.

.. code-block:: bash

    $ echo '{"id":1,"method":"foobar","params":[]}' | ./bin/proxy request | php example/server/minimal.php

The ``proxy`` binary file is used only for this demonstration, it adds the
necessary formatting to the message before passing it to our new language
server (running on STDIO by default).

It should show *something* like:

.. code-block::

    Content-Type: application/vscode-jsonrpc; charset=utf8
    Content-Length: 48

    {"id":1,"result":"Hello World!","jsonrpc":"2.0"}i

At this point you could connect an IDE to your new Language Server, but it
wouldn't do very much.

In the next chapter we'll try and introduce some more
concepts and add some language server functionality.

.. _co-routines: https://amphp.org/amp/coroutines/
