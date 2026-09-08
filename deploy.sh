#!/bin/bash
set -e

echo "=== 1. Pulling latest code ==="
git pull origin main

echo "=== 2. Clearing old caches ==="
php artisan optimize:clear

echo "=== 3. Running database migrations ==="
php artisan migrate --force

echo "=== 4. Ensuring storage link ==="
php artisan storage:link || true

echo "=== 5. Optimizing Configuration & Routes ==="
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo "=== 6. Refreshing Views ==="
# View tidak di-cache permanen agar manifest Vite selalu fresh
php artisan view:clear

echo "=== 7. Auto-bumping Service Worker Cache ==="
SW_FILE="public/sw.js"
if [ -f "$SW_FILE" ]; then
    OLD_VER=$(grep -oP 'wigati-buku-v\K[0-9]+' "$SW_FILE" | head -1)
    if [ -n "$OLD_VER" ]; then
        NEW_VER=$((OLD_VER + 1))
        sed -i "s/wigati-buku-v${OLD_VER}/wigati-buku-v${NEW_VER}/g" "$SW_FILE"
        echo "SW cache updated: v${OLD_VER} -> v${NEW_VER}"
    fi
fi

echo "=== 8. Ensuring Folder Permissions ==="
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "=== Deployment & Optimization Complete! ==="
