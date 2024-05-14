#!/bin/bash
set -e

echo "Deployment started ..."

# Войти в режим обслуживания или вернуть true
# если уже в режиме обслуживания
(/usr/bin/php artisan down) || true

# Загрузить последнюю версию приложения
git checkout production

git pull

# Установить зависимости Composer
/usr/local/bin/composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Очистить старый кэш
/usr/bin/php artisan clear-compiled

# Пересоздать кэш
/usr/bin/php artisan optimize

/usr/bin/php artisan config:clear

# Скомпилировать ресурсы
/root/.nvm/versions/node/v20.13.1/bin/npm run prod

# Запустить миграцию базы данных
/usr/bin/php artisan migrate

# Выход из режима обслуживания
/usr/bin/php artisan up

echo "Deployment finished!"
