# Bagatelle

Bagatelle is a PHP boilerplate bundle for simple websites and APIs.

## Intro

### Is this a micro-framework?

Not really. Bagatelle bundles and pre-configures common components from the PHP ecosystem without introducing much original code. It makes it easy to get started building simple web applications, when complete Laravel or Symfony installations are overkill.

### Why the name?

It's named as a contrast to Symfony, with [Wiktionary](https://en.wiktionary.org/wiki/bagatelle) defining a *bagatelle* as
"a short piece of literature or of instrumental music, typically light or playful in character".

### What's included?

#### Core Symfony components

Bagatelle centers around the [HttpKernel](https://symfony.com/packages/HttpKernel), [Routing](https://symfony.com/packages/Routing), [EventDispatcher](https://symfony.com/packages/EventDispatcher) and [Console](https://symfony.com/packages/Console) packages for building web and CLI applications.

Interacting with HTTP requests and responses is supported using both [HttpFoundation](https://symfony.com/packages/HttpFoundation) and [PSR-7](https://www.php-fig.org/psr/psr-7/) through [PsrHttpMessageBridge](https://symfony.com/packages/PsrHttpMessageBridge).

#### Community cornerstones

Well known community packages [Monolog](https://seldaek.github.io/monolog/) (logging), [PHP-DI](https://php-di.org/) (dependency injection container), [Twig](https://twig.symfony.com/) (templating engine) and [phpdotenv](https://github.com/vlucas/phpdotenv) (environment variables) are bundled and preconfigured.

#### Custom boilerplate

Bagatelle do contain some custom code, most notably route decoration [inspired by Tempest](https://tempestphp.com/2.x/essentials/routing#route-decorators-route-groups). Only one decorator is included by default, `#[Auth]`, which enables a prepared authentication solution that only lacks the final verification implementation (e.g. checking a JWT or Basic Auth username and password).

### What's _not_ included?

Everything else. So no databases, no validation, no session management and no caching.
That's for you to add yourself :)

### Should I use Bagatelle?

If you're just creating a small website or API, sure! However, if you know from the start that you might have a
large scale enterprise application on your hands, it's usually a better idea to start with a proper framework.

## Get Started

_To be added..._
