#!/bin/bash
git pull origin main
php artisan migrate --force
php artisan config:cache
php artisan route:cache
# Tidak cache view agar manifest.json Vite selalu dibaca fresh
php artisan view:clear
