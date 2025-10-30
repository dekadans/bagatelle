# Bagatelle

Bagatelle is a PHP boilerplate bundle for very simple websites and APIs.

## Is this a micro-framework?

Not really. Bagatelle bundles and pre-configures common components from the PHP ecosystem without introducing much original code.
It makes it easy to get started building simple web applications, when complete Laravel or Symfony installations are overkill.

## Why the name?

It's named as a contrast to Symfony, with [Wiktionary](https://en.wiktionary.org/wiki/bagatelle) defining a *bagatelle* as
"a short piece of literature or of instrumental music, typically light or playful in character".

## What's included?

Bagatelle ships with [HTTP processing](https://symfony.com/packages/HttpKernel),
[routing](https://symfony.com/packages/Routing), [events](https://symfony.com/packages/EventDispatcher)
and [console application](https://symfony.com/packages/Console) support (all from Symfony)
as well as templating using [Twig](https://twig.symfony.com/).
It also bundles a PSR-11 dependency injection container ([PHP-DI](https://php-di.org/))
and PSR-3 logger ([Monolog](https://seldaek.github.io/monolog/)).

Basic support for [environment variables](https://symfony.com/packages/Dotenv)
and error handling (HTML and [JSON Problem Details](https://www.rfc-editor.org/rfc/rfc9457.html)) is also implemented.

## What's _not_ included?

Everything else. So no databases, no session management, no caching, no validation and no authentication/authorization.
That's for you to add yourself :)

## Should I use Bagatelle?

If you're just creating a small website or API, sure! However, if you know from the start that you might have a
large scale enterprise application on your hands, it's usually a better idea to start with a proper framework.

## How to get started?

_To be added..._