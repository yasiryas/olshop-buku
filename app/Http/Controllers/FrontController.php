<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();
        $products = Product::with('category')->withStock()->orderBy('id', 'DESC')->take(8)->get();
        $categories = Category::all();
        $articles = Article::with(['user', 'category'])->latest()->take(4)->get();
        return view('front.index', [
            'products' => $products,
            'categories' => $categories,
            'user' => $user,
            'articles' => $articles,
        ]);
    }

    public function product()
    {
        $user = Auth::user();
        $products = Product::with('category')->withStock()->orderBy('id', 'DESC')->get();
        $categories = Category::all();
        return view('front.product', [
            'products' => $products,
            'categories' => $categories,
            'user' => $user,
        ]);
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('search', '');
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->take(20)
            ->get();

        return response()->json($products);
    }

    public function productDetails(Product $product)
    {
        $product = Product::with('category')->withStock()->findOrFail($product->id);

        // Ambil produk terbaru selain produk yang sedang dibaca
        $products = Product::with('category')->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('front.detail-product', [
            'product' => $product,
            'products' => $products
        ]);
    }


    public function search(Request $request)
    {
        $keyword = $request->input('search');
        $products = Product::with('category')->withStock()->where('name', 'LIKE', '%' . $keyword . '%')->get();

        return view('front.search', [
            'products' => $products,
            'keyword' => $keyword
        ]);
    }

    public function category(Category $category)
    {
        $products = Product::where('category_id', $category->id)->with('category')->withStock()->get();

        return view('front.category', [
            'products' => $products,
            'category' => $category
        ]);
    }

    public function blog()
    {
        $articles = Article::with(['user', 'category'])->latest()->paginate(6);
        return view('front.blog', [
            'articles' => $articles
        ]);
    }

    public function article(Article $article)
    {
        $article->load(['category', 'user']);
        $articles = Article::with(['user', 'category'])->latest()->take(4)->get();
        return view('front.article', [
            'article' => $article,
            'articles' => $articles
        ]);
    }

    public function searchArticle(Request $request)
    {
        $keyword = $request->get('q', '');

        $articles = \App\Models\Article::where('title', 'like', "%{$keyword}%")
            ->orWhere('content', 'like', "%{$keyword}%")
            ->latest()
            ->take(20)
            ->get(['id', 'title', 'slug', 'featured_image', 'content']);

        return response()->json($articles);
    }

    public function about()
    {
        return view('front.about');
    }
    public function contact()
    {
        return view('front.contact');
    }
}
