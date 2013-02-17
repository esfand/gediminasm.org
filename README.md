# My blog page

This repository source code is deployed on [gediminasm.org](http://gediminasm.org) my blog page. I have used **Symfony2**
before. But since it was too heavy for such a simple task, it was changed to use a most lightweight version possible. Why
I'm not using **Wordpress**? Well I'm a software engineer and I see fit to make things as light as they can be, I also
share [UNIX philosophy](http://en.wikipedia.org/wiki/Unix_philosophy) and for an engineer it is useful and necessary
to understand language, database, HTTP and many other internals, though always innovate.

Sadly it is also true, that to code a "framework" from scratch is even faster than to use any, which you are unfamiliar
with. Like a concept [vanilla js](http://vanilla-js.com/)

## Requirements

- PHP >= 5.4.0
- Postgresql and php extension, PDO is not used
- PhpUnit >= 3.7.0

## Internals

Here is the whole "framework" structure:

- **framework.php** defines a dispacher and service container.
- **routing** is using standard regular expressions - maybe its time to get more friendly with it ha?
- **error_handler.php** all ways errors are handled.
- **controllers/** a directory where all controllers are registered.
- **services/** a directory where all services are registered, note: **config.php** is visible only in service
initialization scope.
- **commands/** a directory where all console commands are registered.
- **assets/** a directory where all assets are located, before they are being compiled to a production version.
- **models** there are none, but if needed, they can be registered as services.

There is no cache, because there is nothing to cache, except third party stuff like twig or whatever, which is used
additionally. There can't be any faster routing, except file structured one.

## Install

### Nginx configuration example:

    server {
        listen 80;
        server_name blog.lc;

        root /home/gedi/php/blog/public; # point to public directory

        error_log /var/log/nginx/blog.error.log;
        access_log /var/log/nginx/blog.access.log;

        # first check if its a static file, otherwise run through @handler
        location / {
            index index.php;
            try_files $uri @handler;
            #expires 24h;
        }

        # if it was not a static file, execute through index.php
        location @handler {
            rewrite ^(.*)$ /index.php last; # force index.php if it was not a file
        }

        # pass the PHP scripts to fpm socket, NOTE: php-fpm required, otherwise use fastcgi
        location ~ \.php$ {
            fastcgi_pass                    unix:/var/run/php-fpm/php-fpm.sock;
            fastcgi_index                   index.php;
            include                         fastcgi_params;
            fastcgi_param SCRIPT_FILENAME   $document_root$fastcgi_script_name;
            fastcgi_param HTTPS             off;

            # application environment: production, development, testing
            fastcgi_param  APP_ENV          development;
        }
    }

### Install third party:

    curl -s https://getcomposer.org/installer | php && php composer.phar install

### Configure:

    cp config.php.dist config.php

### Install database schema:

    php bin/console blog:db:install

### Install assets:

First, make sure **tmp/** directory is writtable.

    php bin/console core:assets:dump

### Load blog posts to database:

    php bin/console blog:posts:update


