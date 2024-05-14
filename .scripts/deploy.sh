#!/bin/bash
set -e

echo "Deployment started ..."

# Войти в режим обслуживания или вернуть true
# если уже в режиме обслуживания
(php artisan down) || true

# Загрузить последнюю версию приложения
git pull origin production

# Установить зависимости Composer
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Очистить старый кэш
php artisan clear-compiled

# Пересоздать кэш
php artisan optimize

php artisan config:clear

# Скомпилировать ресурсы
npm run prod

# Запустить миграцию базы данных
php artisan migrate

# Выход из режима обслуживания
php artisan up

echo "Deployment finished!"
