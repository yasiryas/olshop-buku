<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach ($urls as $entry)
        <url>
            <loc>{{ $entry['loc'] }}</loc>
            <lastmod>{{ $entry['lastmod'] }}</lastmod>
        </url>
    @endforeach
</urlset>