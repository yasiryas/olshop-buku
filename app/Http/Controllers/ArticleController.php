<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;



class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $articles = Article::when($search, function ($query, $search) {
            $query->where('title', 'like', '%' . $search . '%');
        })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.articles.index', ['articles' => $articles, 'search' => $search]);
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'category_id'    => 'required|exists:categories,id',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('articles', 'public');
            $validated['featured_image'] = $path;
        }

        $validated['user_id'] = auth()->id();

        Article::create($validated);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $categories = Category::all();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'category_id'    => 'required|exists:categories,id',
        ]);

        // Update slug hanya jika judul berubah
        if ($article->title != $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        }

        // Handle image update
        if ($request->hasFile('featured_image')) {
            try {
                // Hapus gambar lama jika ada
                if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
                    Storage::disk('public')->delete($article->featured_image);
                }

                // Simpan gambar baru
                $path = $request->file('featured_image')->store('articles', 'public');
                $validated['featured_image'] = $path;
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal mengupdate gambar: ' . $e->getMessage());
            }
        } else {
            // Pertahankan gambar lama jika tidak ada upload baru
            $validated['featured_image'] = $article->featured_image;
        }
        $article->update($validated);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        // Hapus gambar terkait jika ada
        if ($article->featured_image) {
            Storage::disk('public')->delete($article->featured_image);
        }

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}
