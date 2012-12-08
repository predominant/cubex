Cubex
=====

Apache Virtual Host Config

    <VirtualHost *:80>
      SetEnv CUBEX_ENV development

      DocumentRoot "PATH_TO_CUBEX\cubex\webroot"
      ServerName cubex.local
      ServerAlias www.cubex.local
      ErrorLog "logs/cubex.local-error.log"
      CustomLog "logs/cubex.local-access.log" common

      RewriteEngine on
      RewriteRule ^/js/(.*)     -                       [L,QSA]
      RewriteRule ^/css/(.*)    -                       [L,QSA]
      RewriteRule ^/img/(.*)    -                       [L,QSA]
      RewriteRule ^/favicon.ico -                       [L,QSA]
      RewriteRule ^(.*)$        /index.php?__path__=$1  [B,L,QSA]
    </VirtualHost>

Recommended PHP Modules

- APC


The Cubex Stack

 Application

 Modules | Cubes

 Cubex

 Cubex: sits at the base level, and handles common loading of classes.


 Cubes: are a collection of generic modules that can be used across projects, such as the session layer, or db access


 Modules: similar to cubes, however, these contain specific functionality, e.g. a User module.

          The modules can be used on multiple projects, however, contain specific logic and models

          that can be used by an application.


 Application: ties everything together, handling the users request through to the response.
