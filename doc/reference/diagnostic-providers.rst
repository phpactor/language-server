Diagnostic Providers
====================

Diagnostic providers are invoked when text documents are updated and are
responsible to send diagnostics (e.g. actual or potential problems with the
code) to the client.

Example
-------

Example of a diagnostic provider:

.. literalinclude:: ../../lib/Example/Diagnostics/SayHelloDiagnosticsProvider.php
   :language: php
   :linenos:

.. code-block:: php

    $diagnosticsService = new DiagnosticsService(
        new DiagnosticsEngine($clientApi, new AggregateDiagnosticsProvider(
            $logger,
            new SayHelloDiagnosticsProvider()
        ))
    );

Integration
-----------

Diagnostics are facilitated through the "Diagnostics Service" which in turn
requires the ``DiagnosticsEngine`` which accepts a ``DiagnosticProvider`` -
below we use the ``AggregateDiagnosticsProvider`` which allows you to provide
many diagnostic providers:

.. code-block:: php

    <?php

    $diagnosticsService = new DiagnosticsService(
        new DiagnosticsEngine($clientApi, new AggregateDiagnosticsProvider(
            $logger,
            new SayHelloDiagnosticsProvider()
        ))
    );
