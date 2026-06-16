# Bagatelle

> - A trifle; an insubstantial thing.
> - (literature, music) A short piece of literature or of instrumental music, typically light or playful in character.

_― [bagatelle - Wiktionary, the free dictionary](https://en.wiktionary.org/wiki/bagatelle)_  
(Named in contrast to [Symfony](https://symfony.com/))

## Introduction

Bagatelle is an opinionated PHP boilerplate bundle for simple websites and APIs. It bundles and pre-configures common components from the PHP ecosystem, making it easy to get started while having full control of all the details.

## Contents

Bagatelle bundles core parts of [Symfony](https://symfony.com/) ([HttpKernel](https://symfony.com/packages/HttpKernel), [Routing](https://symfony.com/packages/Routing), [EventDispatcher](https://symfony.com/packages/EventDispatcher) and [Console](https://symfony.com/packages/Console)) with well known components [Monolog](https://seldaek.github.io/monolog/) (PSR-3 logging), [PHP-DI](https://php-di.org/) (PSR-11 dependency injection container), [Twig](https://twig.symfony.com/) (templating engine), [phpdotenv](https://github.com/vlucas/phpdotenv) (environment variables), [nyholm/psr7](https://github.com/Nyholm/psr7) (HTTP messages) and a Docker image based on [FrankenPHP](https://frankenphp.dev/).

### Middleware

Bagatelle also includes the following custom middleware:

- **CORS**: Support for configuring CORS on controller or route level.
- **BasicAuth**: Support for protecting resources using HTTP Basic Authentication.

### Source Code

This project, `tthe/bagatelle`, is the template for new projects. The implementation is in [`tthe/bagatelle-core`](https://github.com/dekadans/bagatelle-core).

## Get Started

### Create project

Create a new application based on Bagatelle (replace "my-project" with your name of choice):

```shell
composer create-project tthe/bagatelle my-project
```

### Environment

Enable the example environment configuration:

```shell
cp .env.example .env
```

### Run application

When developing you can either use PHP's built-in development server:

```shell
php -S localhost:8080 -t public
```

or using Docker:

```shell
docker compose up -d

# or, for production config (using port 80):
SERVER_NAME="your-domain.com" docker compose -f compose.yaml -f compose.prod.yaml up
```

Both development servers will start your Bagatelle-based application at localhost:8080.

The console application is executed using:

```shell
php bin/console.php
```

### Build

Add controllers to `src/Controllers`, console commands to `src/Commands` and configure the dependency injection container in `config/container.php`.

## Documentation

- [HttpKernel and Request/Response Lifecycle](https://symfony.com/doc/current/components/http_kernel.html)
- [Routing](https://symfony.com/doc/current/routing.html) (only parts related to the `#[Route]` attribute are relevant)
- [HttpFoundation](https://symfony.com/doc/current/components/http_foundation.html) and [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) for HTTP messages
- [The Dependency Injection Container](https://php-di.org/doc/)
- [Console Commands](https://symfony.com/doc/current/console.html#creating-a-command)
