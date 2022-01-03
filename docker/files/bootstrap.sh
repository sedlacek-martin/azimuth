#!/bin/sh

if [[ ! -f app/config/parameters.yml ]]; then
    cp /init/parameters.yml app/config/parameters.yml
fi

if [[ ! -d vendor ]]; then
    composer install --prefer-dist
fi

while !(mysqladmin -ucocorico -pcocorico -hmysql ping &> /dev/null); do
    sleep 1
done

RESULT=`mysqlshow --host=mysql --user=cocorico --password=cocorico cocorico | grep -o user -m 1`

if [ "$RESULT"  != "user" ]; then
    php bin/console doctrine:schema:update --force
    php bin/console doctrine:fixtures:load -n
fi

if [[ ! -f web/json/currencies.json ]]; then
    php bin/console cocorico:currency:update
fi

if [[ ! -d web/bundles ]]; then
    php bin/console assets:install --symlink
fi

if [[ ! -d web/css/compiled || ! -d web/js/compiled ]]; then
    php bin/console assetic:dump
fi

echo 'cocorico is alive'
