Generar entidades nuevas
php bin/console make:entity User

Iniciar api
symfony server:start


Parar api
symfony server:stop

symfony check:requirements


Sincronizar
php bin/console doctrine:migrations:diff

Migrar nuevas entidades
php bin/console doctrine:migrations:migrate
