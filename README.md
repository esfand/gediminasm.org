# My blog page

This repository source code is deployed on [gediminasm.org](http://gediminasm.org) my blog page. I have used **Symfony2**
before. But since it is too heavy for such a simple task, it was changed to use a most lightweight version possible. Why
I'm not using **Wordpress**? Well I'm a software engineer and I see fit to make things as light as they can be, I also
share [UNIX philosophy](http://en.wikipedia.org/wiki/Unix_philosophy) and for an engineer it is useful and necessary
to understand language, database, HTTP and many other internals.

Sadly it is also true, that to code a "framework" from scrach is even faster than to use any, which you are unfamiliar
with. Like take a peek at [vanilla js](http://vanilla-js.com/)

## Internals

Here is the whole blog (framework) structure:

- **framework.php** defines a dispacher and service container.
- **routing** is using standard regular expressions - maybe its time to get more friendly with it ha?
- **error_handler.php** handles all errors exceptions.
- **controllers/** a directory where all controllers are registered.
- **services/** a directory where all services are registered, note: **config.php** is visible only in service
initialization scope.
- **commands/** a directory where all console commands are registered.
- **assets/** a directory where all assets are located, before they are being compiled to a production version.

There is no cache, because there is nothing to cache, except third party stuff like twig or whatever is used
additionally. There can't be any faster routing, except file structured one.

## Install


