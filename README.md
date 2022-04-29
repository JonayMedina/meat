
About
-----

Procasa / Meat House (tienda retail) es una empresa constituida con capital guatemalteco, con más de 45 años de experiencia en la industria de alimentos. En Procasa, se procesan, comercializan y distribuyen productos cárnicos de alta calidad; Res, Cerdo, Pollo, Pavo, Tortas y más. Procasa decidió también vender de manera online por lo cual se vio en la necesidad de actualizar su sitio web y volverlo un e-commerce. A continuación se define la Web-responsive de Procasa pantalla por pantalla.

Documentation
-------------

Documentation is available at [symfony.com](http://symfony.com).

Installation
------------

```bash
$ cd project
# Yarn debe estar en al menos la versión 1, sino dará problemas.
$ yarn install
$ yarn build
$ php bin/console assets:install
$ php bin/console sylius:theme:assets:install
$ php bin/console server:start
$ open http://localhost:8000/
```

Troubleshooting
---------------

If something goes wrong, errors & exceptions are logged at the application level:

```bash
$ tail -f var/log/prod.log
$ tail -f var/log/dev.log
```
