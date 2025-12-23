<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $categories = Category::when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', ['categories' => $categories, 'search' => $search]);
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
            'name' => 'required|string|max:255',
            'icon' => 'required|image|mimes:jpeg,png,jpg,svg',
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

            return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['System error!' . $e->getMessage()],
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
            'name' => 'sometimes|string|max:255',
            'icon' => 'sometimes|image|mimes:jpeg,png,jpg,svg',
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

            return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['System error!' . $e->getMessage()],
            ]);
            throw $error;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
        try {
            $category->delete();
            return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['System error!' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
