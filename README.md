Phpactor Language Server
========================

[![Build Status](https://travis-ci.org/phpactor/language-server.svg?branch=master)](https://travis-ci.org/phpactor/language-server)

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

See the [Language Server
Specification](https://microsoft.github.io/language-server-protocol/specification)
for a list of methods which you can implement with this package.

Documentation
-------------


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

Contributing
------------

Contributions are welcome.

License
-------

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
