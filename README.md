# Bagatelle

> - A trifle; an insubstantial thing.
> - (literature, music) A short piece of literature or of instrumental music, typically light or playful in character.

_― [bagatelle - Wiktionary, the free dictionary](https://en.wiktionary.org/wiki/bagatelle)_  
(Named in contrast to [Symfony](https://symfony.com/))

## Introduction

Bagatelle is a PHP boilerplate bundle for simple websites and APIs.

It's not really a micro-framework, instead, Bagatelle bundles and pre-configures common components from the PHP ecosystem without introducing much original code. It makes it easy to get started building simple web applications, when complete framework installations are overkill.

## Contents

Bagatelle centers around Symfony's [HttpKernel](https://symfony.com/packages/HttpKernel), [Routing](https://symfony.com/packages/Routing), [EventDispatcher](https://symfony.com/packages/EventDispatcher) and [Console](https://symfony.com/packages/Console) packages for building web and CLI applications.

Well known packages [Monolog](https://seldaek.github.io/monolog/) (PSR-3 logging), [PHP-DI](https://php-di.org/) (PSR-11 dependency injection container), [Twig](https://twig.symfony.com/) (templating engine), [phpdotenv](https://github.com/vlucas/phpdotenv) (environment variables) and [nyholm/psr7](https://github.com/Nyholm/psr7) are bundled and preconfigured as well.

A basic but functional implementation for adding middleware to routes and controllers is also prepared. 

Additionally, a Docker image based on [FrankenPHP](https://frankenphp.dev/) is ready for development and production use-cases.

## Get Started

### Create project

...

### Run application

When developing you can either use PHPs built-in development server:

```shell
php -S localhost:8080 -t public
```

or using Docker:

```shell
docker compose up -d

# or, for production config:
SERVER_NAME="your-domain.com" docker compose -f compose.yaml -f compose.prod.yaml up
```

Both will start your Bagatelle-based application at localhost:8080.

The console application is executed using:

```shell
php bin/console.php
```

## Documentation

- [HttpKernel and Request/Response Lifecycle](https://symfony.com/doc/current/components/http_kernel.html)
- [Routing](https://symfony.com/doc/current/routing.html) (only parts related to the `#[Route]` attribute are relevant)
- [HttpFoundation](https://symfony.com/doc/current/components/http_foundation.html) and [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) for HTTP messages
- [The Dependency Injection Container](https://php-di.org/doc/)
- [Console Commands](https://symfony.com/doc/current/console.html#creating-a-command)

... add examples ...