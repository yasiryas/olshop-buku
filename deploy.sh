#!/bin/bash
git pull origin main
php artisan migrate --force
php artisan config:cache
php artisan route:cache
# Tidak cache view agar manifest.json Vite selalu dibaca fresh
php artisan view:clear
php artisan cache:clear

# Auto-bump SW cache version agar browser invalidate asset lama
SW_FILE="public/sw.js"
if [ -f "$SW_FILE" ]; then
    OLD_VER=$(grep -oP 'wigati-buku-v\K[0-9]+' "$SW_FILE" | head -1)
    NEW_VER=$((OLD_VER + 1))
    sed -i "s/wigati-buku-v${OLD_VER}/wigati-buku-v${NEW_VER}/g" "$SW_FILE"
    echo "SW cache: v${OLD_VER} → v${NEW_VER}"
fi
