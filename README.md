Phpactor Language Server
========================

![CI](https://github.com/phpactor/language-server/workflows/CI/badge.svg)

This package provides a platform for *building* a Language Server according to
the [Language Server Specification](https://microsoft.github.io/language-server-protocol/specification)

- :heavy_check_mark: Can run as either a TCP server or on STDIO.
- :heavy_check_mark: Multiple connections.
- :heavy_check_mark: Text document synchronization.
- :heavy_check_mark: Background services.
- :heavy_check_mark: Bi-directional requests.
- :heavy_check_mark: Commands.
- :heavy_check_mark: Request cancellation.
- :heavy_check_mark: Initialization handling.
- :heavy_check_mark: Up-to-date and self-instantiating [protocol classes](https://github.com/phpactor/language-server-protocol).

See the [Language Server
Specification](https://microsoft.github.io/language-server-protocol/specification)
for a list of methods which you can implement with this package.

Documentation
-------------

Documentation can be found on [readthedocs](https://language-server-platform.readthedocs.io/en/latest/).

Installing
----------

```
$ composer require phpactor/language-server
```

Running the tests
-----------------

With composer:

```
$ composer integrate
```

or:

```
$ ./vendor/bin/phpunit
$ ./vendor/bin/phpstan analyse
$ ./vendor/bin/php-cs-fixer fix
```

Built With
----------

- [Amphp](https://amphp.org/): Event-driven concurrency framework.

License
-------

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

Contributing
------------

This package is open source and welcomes contributions! Feel free to open a
pull request on this repository.

Support
-------

- Create an issue on the main [Phpactor](https://github.com/phpactor/phpactor) repository.
- Join the `#phpactor` channel on the Slack [Symfony Devs](https://symfony.com/slack-invite) channel.

