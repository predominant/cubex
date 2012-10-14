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
