Creating a Language Server
==========================

In the previous tutorial we used the ``ClosureDispatcherFactory``. This is
fine, but let's now implement our own application - ``AcmeLS`` and give it a
dedicated dispatcher factory ``AcmeLsDispatcherFactory``. This will be the
ingress for a new session:

.. literalinclude:: ../../example/server/acme-ls.php
   :language: php
   :linenos:

The dispatcher is responsible for bootstrapping your language server session
and creating all the necessary classes that you will need. You might, for
example, instantiate a container here using some initialization options form
the client.

The Language Server invokes the factory method of this class with two
necessary dependencies: ``MessageTransmitter`` and the
``InitializeParams``.

Let's just jump in at the deep end:

.. literalinclude:: ../../example/server/acme-ls/AcmeLsDispatcherFactory.php
   :language: php
   :linenos:

- ``MessageTransmitter``: This class is provided by the Language Server and
  allows you to send messages to the connected client. This is quite
  low-level, instead you should use the ``ClientApi``.
- ``InitializeParams``: The initialization parameters provided by the client.
- ``ResponseWatcher``: Class which tracks requests made by the server *to* the
  client and can resolve responses, used as a dependency for...
- ``ClientApi``: This class allows you to send (and receive) messages to the
  client. It provides a convenient API
  ``$clientApi->window()->showMessage()->error('Foobar')``. In cases where the
  API doesn't provide what you need you can use the ...
- ``RpcClient`` which allows you to send *requests* and *notifications* to
  the client.
- ``ServiceProviders``, ``PingProvider``, ``ServiceManager``: Ping provider is
  an annoying service which pings your client for no reason at all, it is an
  example background process. See  :doc:`../reference/service-providers` for
  more information on services.
- ``Workspace``: This class can keeps track of LSP text documents.
- ``EventDispatcher``: Required by some middlewares to transmit events which
  can be handled by ``Psr\EventDispatcher\ListenerProviderInterface`` classes.
  We use:

  - ``ServiceListener``: responsible to start all the services when the server
    is ``initialized``.
  - ``WorkspaceListener``: will update the above mentioned ``Workspace`` based
    on events emitted by the ``TextDocumentHandler``.

- ``Handlers``: Method handlers are responsible for handling incoming method
  requests, this is the main extension point, see :doc:`../reference/handlers`
- ``HandlerMethodRunner``: This class is responsible for calling methods on
  your class and converting the array of parameters from the request to match
  the parameters on a handler's method. Find out more in :doc:`../reference/handlers`.

The RPC method handlers:

- ``TextDocumentHandler``: Handles all text document notifications from the
  client (i.e. text document synchronization). It emmits events.
- ``ServiceHandler``: Non-protocol handler for starting/stopping monitoring
  services.
- ``CommandHandler``: Clients can execute commands (e.g. refactor something)
  on the server, this class handlers that. See :doc:`../reference/commands`.
- ``ExitHandler``: Handles ``shutdown`` notifications from the client.

Finally we build the middleware dispatcher with the middlewares which will
handle the request:

- ``ErrorHandlingMiddleware``: Will catch any errors thrown by succeeding
  middlewares and log them. As a long running process we don't want to exit
  each time something goes wrong.
- ``InitializeMiddleware``: This middleware *responds* to the initialize
  request. It also allows your handlers to inject capabiltities into the
  response, more in :doc:`../reference/handlers`.
- ``ResponseHandlingMiddleware``: Catch responses to requests made *by* the
  server, and resolves them using our ``ResponseWatcher``.
- ``CancellationMiddleware``: Often the client knows that a request is no
  longer required, and it request that that request be *cancelled* (imagine a
  long-running search). This middleware intercepts the ``$/cancelRequest``
  notifications and tells the runner to cancel them.
- ``HandlerMiddleware``: The final destination - will forward the request to
  the handler runner which will dispatch our :doc:`handlers <../reference/handlers>`

In your application you might choose to connect all of this magic in a
dependency injection container.
