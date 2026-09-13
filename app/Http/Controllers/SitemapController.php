<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $urls = collect();

        foreach ([
            '/',
            '/blog',
            '/product',
            '/about',
            '/contact',
        ] as $path) {
            $urls->push(['loc' => url($path), 'lastmod' => now()->toDateString()]);
        }

        foreach (Category::all() as $category) {
            $urls->push([
                'loc' => route('front.product.category', $category),
                'lastmod' => now()->toDateString(),
            ]);
        }

        foreach (Product::orderByDesc('updated_at')->get(['slug', 'updated_at']) as $product) {
            $urls->push([
                'loc' => route('front.product.details', $product->slug),
                'lastmod' => $product->updated_at->toDateString(),
            ]);
        }

        foreach (Article::where('is_published', true)->orderByDesc('updated_at')->get(['slug', 'updated_at']) as $article) {
            $urls->push([
                'loc' => route('front.article.details', $article->slug),
                'lastmod' => $article->updated_at->toDateString(),
            ]);
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots()
    {
        $sitemapUrl = url('sitemap.xml');

        $rules = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /cart',
            'Disallow: /product_transactions',
            'Disallow: /search',
            'Disallow: /*?page=',
            '',
            "Sitemap: {$sitemapUrl}",
        ];

        return Response::make(implode("\n", $rules), 200, ['Content-Type' => 'text/plain']);
    }
}