# Bagatelle

> - A trifle; an insubstantial thing.
> - (literature, music) A short piece of literature or of instrumental music, typically light or playful in character.

_― [bagatelle - Wiktionary, the free dictionary](https://en.wiktionary.org/wiki/bagatelle)_

## Introduction

Bagatelle is an opinionated PHP micro framework based on Symfony for simple websites and APIs.

## Contents

Bagatelle bundles core parts of [Symfony](https://symfony.com/) with well known components [Monolog](https://seldaek.github.io/monolog/) (PSR-3 logging), [PHP-DI](https://php-di.org/) (PSR-11 dependency injection container), [Twig](https://twig.symfony.com/) (templating engine), [phpdotenv](https://github.com/vlucas/phpdotenv) (environment variables), [nyholm/psr7](https://github.com/Nyholm/psr7) (HTTP messages) and a Docker image based on [FrankenPHP](https://frankenphp.dev/).

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

### Controllers & Routing

Controllers are placed in the path defined by `PATH_CONTROLLERS` (`src/Controllers` by default).

Routes are bound using the standard Symfony `#[Route]` attribute, or the bundled method attributes `#[Get]`, `#[Post]` etc. See [documentation](https://symfony.com/doc/current/routing.html) (not all features are enabled out-of-the-box).

HTTP message representations is available using either [HttpFoundation](https://symfony.com/doc/current/components/http_foundation.html) or [PSR-7 interfaces](https://www.php-fig.org/psr/psr-7/).

### Console

Console commands are placed in the path defined by `PATH_COMMANDS` (`src/Commands` by default). They are loaded automatically using the `#[AsCommand]` attribute.

See [documentation](https://symfony.com/doc/current/console.html).

### Dependency Injection

Bagatelle uses [PHP-DI](https://php-di.org/doc/) as its dependency injection container.

The container is configured by the file identified by `PATH_CONTAINER` (`config/container.php` by default).

A few notable services bound by Bagatelle:

| Container key                                                | Service description                        |
|--------------------------------------------------------------|--------------------------------------------|
| `\Psr\Container\ContainerInterface`                          | The container itself.                      |
| `\Psr\Log\LoggerInterface`                                   | Monolog logger.                            |
| `\Psr\EventDispatcher\EventDispatcherInterface`              | Dispatching events to subscribers.         |
| `\Symfony\Component\Routing\Generator\UrlGeneratorInterface` | Generating URLs to routes.                 |
| `\Twig\Environment`                                          | Twig instance for parsing templates/views. |

### Middleware

Middleware can be implemented using `\tthe\Bagatelle\Middleware\MiddlewareInterface` and applied to controllers or routes using the attribute: `#[Middleware(\Name\Of\Middleware::class)]`

These middleware are included by default:

| Name                             | Purpose                                               |
|----------------------------------|-------------------------------------------------------|
| `\tthe\Bagatelle\Http\CORS`      | Configuring CORS on controller or route level.        |
| `\tthe\Bagatelle\Http\BasicAuth` | Protecting resources using HTTP Basic Authentication. |
