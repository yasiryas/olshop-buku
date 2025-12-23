<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // $products = Product::with('category')->orderBy('id', 'DESC')->get();
        // return view('admin.products.index', ['products' => $products]);

        $products = Product::with('category')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('id', 'DESC')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', ['products' => $products, 'search' => $search]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $categories = Category::all();
        return view('admin.products.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'about' => 'required|string',
            'category_id' => 'required|integer',
            'price' => 'required|integer',
            'photo' => 'required|image|mimes:jpeg,png,jpg,svg',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('product_photos', 'public');
                $validated['photo'] = $photoPath;
            }
            $validated['slug'] = Str::slug($request->name);
            $newProduct = Product::create($validated);

            DB::commit();

            return redirect()->route('admin.products.index')->with('success', 'Category created successfully.');
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
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
        $categories = Category::all();
        return view('admin.products.edit', ['product' => $product,  'categories' => $categories,]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'about' => 'sometimes|string',
            'category_id' => 'sometimes|integer',
            'price' => 'sometimes|integer',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,svg',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('product_photos', 'public');
                $validated['photo'] = $photoPath;
            }
            $validated['slug'] = Str::slug($request->name);
            $product->update($validated);
            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
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
    public function destroy(Product $product)
    {
        //
        try {
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['System error!' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
