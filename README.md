Phpactor Language Server
========================

[![Build Status](https://travis-ci.org/phpactor/language-server.svg?branch=master)](https://travis-ci.org/phpactor/language-server)

This package provides a platform for *building* a Language Server according to
the [Language Server Specification](https://microsoft.github.io/language-server-protocol/specification)

- :tick: Can run as either a TCP server (accepting many connections) or a STDIO
  server (invoked by the client).
- :tick: Multiple sessions.
- :tick: Text document synchronization.
- :tick: Background services.
- :tick: Bi-directional requests.
- :tick: Commands.
- :tick: Request cancellation.
- :tick: Initialization handling.

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

- [Amphp](https://amphp.org/): Event-driver concurrency framework.

Contributing
------------

Contributions are welcome.

Versioning
----------

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags). 

License
-------

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
