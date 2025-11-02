# Bagatelle

Bagatelle is a PHP boilerplate bundle for very simple websites and APIs.

## Intro

### Is this a micro-framework?

Not really. Bagatelle bundles and pre-configures common components from the PHP ecosystem without introducing much original code.
It makes it easy to get started building simple web applications, when complete Laravel or Symfony installations are overkill.

### Why the name?

It's named as a contrast to Symfony, with [Wiktionary](https://en.wiktionary.org/wiki/bagatelle) defining a *bagatelle* as
"a short piece of literature or of instrumental music, typically light or playful in character".

### What's included?

Bagatelle ships with:

- Symfony's [event based](https://symfony.com/packages/EventDispatcher) [HTTP processing](https://symfony.com/packages/HttpKernel) and [routing](https://symfony.com/packages/Routing)
- [Console application](https://symfony.com/packages/Console) support (also Symfony)
- Templating using [Twig](https://twig.symfony.com/)
- [PHP-DI](https://php-di.org/) as dependency injection container
- [Monolog](https://seldaek.github.io/monolog/) for logging

Additionally, basic error handling is implemented and
a simple authentication solution is prepared but without any details implemented.

In other words, things that are good to have for most use cases.

### What's _not_ included?

Everything else. So no databases, no session management, no caching and no validation.
That's for you to add yourself :)

### Should I use Bagatelle?

If you're just creating a small website or API, sure! However, if you know from the start that you might have a
large scale enterprise application on your hands, it's usually a better idea to start with a proper framework.

## Get Started

_To be added..._
