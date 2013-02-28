# Doctrine Behavioral Extension Demo on Symfony2

This blog is built on Symfony2 using [symfony-standard edition][symfony_standard]. The purpose
of this product being as open source blog, is that people could see how the
[demo][gedmo_demo] of [gedmo extensions][gedmo_extensions] is made and
could reuse it or have a basic understanding on how to use it in any project.

[gedmo_extensions]: https://github.com/l3pp4rd/DoctrineExtensions "Gedmo behavioral doctrine2 extensions"
[gedmo_demo]: http://demo.gediminasm.org "Test extensions on my blog"
[symfony_standard]: https://github.com/symfony/symfony-standard "Symfony2 standard edition"

## Setup

Run the following commands:

    git clone http://github.com/l3pp4rd/gediminasm.org.git ext_d2_demo
    cd ext_d2_demo
    git checkout -b symfony2 origin/symfony2
    mkdir app/cache app/logs
    cp app/config/parameters.yml.dist app/config/parameters.yml

Configure your database connection settings in: **app/config/parameters.yml** tested with **postgres** and **mysql**

Proceed with installation:
**Note:** it uses latest versions of symfony2 and other vendors. There might be conflicts.

    wget http://getcomposer.org/composer.phar
    php composer.phar install
    php app/console assets:install web
    php app/console assetic:dump
    php app/console doctrine:database:create
    php app/console doctrine:schema:create
    php app/console demo:reload

Now when you visit **/app_dev.php** you should see extension demo page

## Nginx vhost

If you use **Nginx** your vhost could look like:

    server {
        listen 80;
        server_name demo.local;
        root /home/gedi/php/ext_d2_demo/web;

        error_log /var/log/nginx/demo.error.log;
        access_log /var/log/nginx/demo.access.log;

        rewrite ^/app\.php/?(.*)$ /$1 permanent;

        location / {
            index app.php;
            if (-f $request_filename) {
                break;
            }
            rewrite ^(.*)$ /app.php last;
        }

        location ~ ^/(app|app_dev|app_test)\.php(/|$) {
            # archlinux
            fastcgi_pass                    unix:/var/run/php-fpm/php-fpm.sock;
            # ubuntu
            # fastcgi_pass                    unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info         ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            fastcgi_param  HTTPS            off;
        }
    }

## Optional

To override default configuration options use:

    cp app/config/config_dev.yml app/config/config_dev.local.yml

