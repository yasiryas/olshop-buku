<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $categories = Category::withCount('products')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.partials.categories_list', compact('categories', 'search'));
        }

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    /**
     * Data edit satu kategori (dimuat on-demand untuk modal edit).
     */
    public function editData(Category $category)
    {
        abort_unless(request()->expectsJson() || request()->ajax(), 404);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'icon' => Storage::url($category->icon),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'icon' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('category_icons', 'public');
                $validated['icon'] = $iconPath;
            }
            $validated['slug'] = Str::slug($request->name);
            $newCategory = Category::create($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['message' => 'Kategori berhasil dibuat.']);
            }

            return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['system_error' => ['Terjadi kesalahan sistem: '.$e->getMessage()]],
                ], 422);
            }

            $error = ValidationException::withMessages([
                'system_error' => ['Terjadi kesalahan sistem: '.$e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
        return view('admin.categories.edit', ['category' => $category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        //
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $category->id,
            'icon' => 'sometimes|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('category_icons', 'public');
                $validated['icon'] = $iconPath;
            }
            $validated['slug'] = Str::slug($request->name);
            $category->update($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['message' => 'Kategori berhasil diperbarui.']);
            }

            return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['system_error' => ['Terjadi kesalahan sistem: '.$e->getMessage()]],
                ], 422);
            }

            $error = ValidationException::withMessages([
                'system_error' => ['Terjadi kesalahan sistem: '.$e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        $linkedProducts = (int) $category->products()->count();
        $linkedArticles = (int) $category->articles()->count();

        if ($linkedProducts > 0 || $linkedArticles > 0) {
            $message = "Kategori tidak dapat dihapus karena masih berisi $linkedProducts produk dan $linkedArticles artikel. Pindahkan atau hapus dulu isinya.";

            if ($request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('admin.categories.index')
                ->with('error', $message);
        }

        $category->delete();

        if ($request->ajax()) {
            return response()->json(['message' => 'Kategori berhasil dihapus.']);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
