CHANGELOG
=========

8.0
---

- PHP 8.x updates
- Add support for incrementally updating text documents
- Breaking changes content changes are now DTOs instead of arrays.

7.0
---

- Dropped dependency on `Safe`
- Bumped minimum version of PHP to 8.1

6.0
---

- Refactored diagnostics engine to execute diagnostic providers in parallel

5.0
---

- Code actions must implement the `describe(): string` method.

1.0.2
-----

- Add support for file events

0.2.0
-----

Rewritten to use Amphp.
