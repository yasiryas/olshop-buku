<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        if ($request->ajax()) {
            return view('admin.partials.products_list', compact('products', 'search'));
        }

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
            'categories' => Category::all(),
        ]);
    }

    /**
     * Data edit satu produk (dimuat on-demand untuk modal edit).
     */
    public function editData(Product $product)
    {
        abort_unless(request()->expectsJson() || request()->ajax(), 404);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => (string) $product->price,
            'about' => $product->about,
            'category_id' => $product->category_id,
            'photo' => Storage::url($product->photo),
        ]);
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
        $request->merge(['price' => (int) str_replace('.', '', $request->price)]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'about' => 'required|string',
            'category_id' => 'required|integer',
            'price' => 'required|integer|min:1',
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
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

            return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['Terjadi kesalahan sistem: ' . $e->getMessage()],
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
        if ($request->has('price')) {
            $request->merge(['price' => (int) str_replace('.', '', $request->price)]);
        }
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'about' => 'sometimes|string',
            'category_id' => 'sometimes|integer',
            'price' => 'sometimes|integer',
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('photo')) {
                if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                    Storage::disk('public')->delete($product->photo);
                }
                $photoPath = $request->file('photo')->store('product_photos', 'public');
                $validated['photo'] = $photoPath;
            }
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }
            $product->update($validated);
            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['Terjadi kesalahan sistem: ' . $e->getMessage()],
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
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $error = ValidationException::withMessages([
                'system_error' => ['Terjadi kesalahan sistem: ' . $e->getMessage()],
            ]);
            throw $error;
        }
    }
}
