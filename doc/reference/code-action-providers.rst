Code Action Provider
====================

Code action providers can be implemented to enable you to suggest
:doc:`commands <../reference/commands>` which can be executed on a given text document and
range.

Example
-------

Example of a command:

.. literalinclude:: ../../lib/Example/CodeAction/SayHelloCodeActionProvider.php
   :language: php
   :linenos:

It unconditionally provides two code actions: ``Alice`` and ``Bob``. It
references a previously registered :doc:`commands <../reference/commands>` such as:

.. literalinclude:: ../../lib/Example/Command/SayHelloCommand.php
   :language: php
   :linenos:
