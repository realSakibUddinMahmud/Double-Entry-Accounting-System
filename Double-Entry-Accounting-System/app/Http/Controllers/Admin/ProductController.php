<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tax;
use App\Models\Brand;
use App\Models\Image;
use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use App\Models\Unit; // Add this at the top with other use statements
use App\Models\CustomFieldValue;

class ProductController extends Controller
{
    use UsesTenantConnection;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'productStores', 'images'])->latest();
        
        // Search by product name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        
        // Filter by store
        if ($request->filled('store_id')) {
            $query->whereHas('productStores', function($q) use ($request) {
                $q->where('store_id', $request->store_id);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $products = $query->paginate(20)->withQueryString();
        
        // Get categories, brands, and stores for filters
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $stores = Store::where('status', 1)->orderBy('name')->get();
        $customFields = CustomField::where('model_type', 'App\Models\Product')->get();

        return view('admin.product.index', compact('products', 'categories', 'brands', 'stores', 'customFields'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $productNameSuggestions = DB::connection('landlord')->table('products')->pluck('name')->unique();
        $stores = Store::where('status',1)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $taxes = Tax::orderBy('name')->get();
        $productFields = CustomField::where('model_type', 'App\Models\Product')->get();

        return view('admin.product.create', compact('brands', 'categories', 'productNameSuggestions', 'stores', 'units', 'taxes', 'productFields'));
    }

    public function store(Request $request)
    {
        $connection = $this->getConnectionName();

        $validator = Validator::make($request->all(), [
            'store_id'         => ['required', Rule::exists($connection . '.stores', 'id')],
            'name'             => 'required|string|max:255',
            'sku'              => 'nullable|string|max:100',
            'brand_id'         => ['nullable', Rule::exists($connection . '.brands', 'id')],
            'category_id'      => ['required', Rule::exists($connection . '.categories', 'id')],
            'barcode'          => 'nullable|string|max:100',
            'base_unit_id'     => ['required', Rule::exists($connection . '.units', 'id')],
            'purchase_unit_id' => ['required', Rule::exists($connection . '.units', 'id')],
            'sales_unit_id'    => ['required', Rule::exists($connection . '.units', 'id')],
            'purchase_cost'    => 'required|numeric|min:0',
            'cogs'             => 'required|numeric|min:0',
            'sales_price'      => 'required|numeric|min:0',
            'tax_id'           => ['nullable', Rule::exists($connection . '.taxes', 'id')],
            'tax_method'       => ['required', Rule::in(['exclusive', 'inclusive'])],
            'description'      => 'nullable|string',
            'images'           => 'nullable|array',
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $validator->validate();

        try {
            DB::beginTransaction();

            // 1. Save product data
            $product = Product::create([
                'category_id' => $request->category_id,
                'brand_id'    => $request->brand_id,
                'name'        => $request->name,
                'description' => $request->description,
                'sku'         => $request->sku,
                'barcode'     => $request->barcode,
                'status'      => true,
            ]);

            // 2. Save product_store data using the model
            ProductStore::create([
                'store_id'         => $request->store_id,
                'product_id'       => $product->id,
                'base_unit_id'     => $request->base_unit_id,
                'purchase_unit_id' => $request->purchase_unit_id,
                'sales_unit_id'    => $request->sales_unit_id,
                'purchase_cost'    => $request->purchase_cost,
                'cogs'             => $request->cogs,
                'sales_price'      => $request->sales_price,
                'tax_id'           => $request->tax_id,
                'tax_method'       => $request->tax_method,
                'status'           => true,
            ]);

            // 3. Save images to polymorphic table using the model
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $sku = $product->sku ?: 'product-' . $product->id;
                    $extension = $image->getClientOriginalExtension();
                    $filename = $sku . '-' . uniqid() . '-' . $index . '.' . $extension;
                    $path = $image->storeAs('products', $filename, 'public');

                    Image::create([
                        'path'           => $path,
                        'imageable_type' => Product::class,
                        'imageable_id'   => $product->id,
                    ]);
                }
            }

            // 4. Save additional fields (custom fields) using CustomFieldValue model
            if ($request->has('additional_fields') && is_array($request->additional_fields)) {
                foreach ($request->additional_fields as $fieldName => $fieldValue) {
                    $customField = CustomField::where('model_type', 'App\Models\Product')
                        ->where('name', $fieldName)
                        ->first();

                    if ($customField) {
                        CustomFieldValue::create([
                            'model_type'      => 'App\Models\Product',
                            'model_id'        => $product->id,
                            'custom_field_id' => $customField->id,
                            'value'           => is_array($fieldValue) ? implode(',', $fieldValue) : $fieldValue,
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        $stores = Store::all();
        $units = Unit::all();
        $taxes = Tax::all();

        // Get the first related product_store record for this product (if any)
        $productStore = $product->productStores()->first();

        // Get images for this product
        $images = $product->images;

        // Get all custom fields for products
        $customFields = CustomField::where('model_type', 'App\Models\Product')->get();

        return view('admin.product.edit', compact(
            'product',
            'categories',
            'brands',
            'stores',
            'units',
            'taxes',
            'productStore',
            'images',
            'customFields'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $connection = $this->getConnectionName();
    
        $validator = Validator::make($request->all(), [
            'category_id'      => ['required', Rule::exists($connection . '.categories', 'id')],
            'brand_id'         => ['nullable', Rule::exists($connection . '.brands', 'id')],
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'sku'              => 'nullable|string|max:100',
            'barcode'          => 'nullable|string|max:100',
            'status'           => 'required|boolean',
            'store_id'         => ['required', Rule::exists($connection . '.stores', 'id')],
            'base_unit_id'     => ['required', Rule::exists($connection . '.units', 'id')],
            'purchase_cost'    => 'required|numeric|min:0',
            'cogs'             => 'required|numeric|min:0',
            'sales_price'      => 'required|numeric|min:0',
            'tax_id'           => ['nullable', Rule::exists($connection . '.taxes', 'id')],
            'tax_method'       => ['required', Rule::in(['exclusive', 'inclusive'])],
            'images'           => 'nullable|array',
            'images.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);
    
        $validator->validate();
    
        try {
            DB::beginTransaction();
    
            // Update product main data
            $product->update([
                'category_id' => $request->category_id,
                'brand_id'    => $request->brand_id,
                'name'        => $request->name,
                'description' => $request->description,
                'sku'         => $request->sku,
                'barcode'     => $request->barcode,
                'status'      => $request->status,
            ]);
    
            // Update or create product_store data
            $productStore = $product->productStores()->first();
            if ($productStore) {
                $productStore->update([
                    'store_id'         => $request->store_id,
                    'base_unit_id'     => $request->base_unit_id,
                    'purchase_unit_id' => $request->purchase_unit_id,
                    'sales_unit_id'    => $request->sales_unit_id,
                    'purchase_cost'    => $request->purchase_cost,
                    'cogs'             => $request->cogs,
                    'sales_price'      => $request->sales_price,
                    'tax_id'           => $request->tax_id,
                    'tax_method'       => $request->tax_method,
                ]);
            } else {
                ProductStore::create([
                    'product_id'       => $product->id,
                    'store_id'         => $request->store_id,
                    'base_unit_id'     => $request->base_unit_id,
                    'purchase_unit_id' => $request->purchase_unit_id,
                    'sales_unit_id'    => $request->sales_unit_id,
                    'purchase_cost'    => $request->purchase_cost,
                    'cogs'             => $request->cogs,
                    'sales_price'      => $request->sales_price,
                    'tax_id'           => $request->tax_id,
                    'tax_method'       => $request->tax_method,
                    'status'           => true,
                ]);
            }
    
            // Handle new images upload
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $sku = $product->sku ?: 'product-' . $product->id;
                    $extension = $image->getClientOriginalExtension();
                    $filename = $sku . '-' . uniqid() . '-' . $index . '.' . $extension;
                    $path = $image->storeAs('products', $filename, 'public');
    
                    Image::create([
                        'path'           => $path,
                        'imageable_type' => Product::class,
                        'imageable_id'   => $product->id,
                    ]);
                }
            }
    
            // --- Additional Fields Update Logic ---
            if ($request->has('additional_fields') && is_array($request->additional_fields)) {
                foreach ($request->additional_fields as $fieldName => $fieldValue) {
                    $customField = CustomField::where('model_type', 'App\Models\Product')
                        ->where('name', $fieldName)
                        ->first();
    
                    if ($customField) {
                        CustomFieldValue::updateOrCreate(
                            [
                                'model_type'      => 'App\Models\Product',
                                'model_id'        => $product->id,
                                'custom_field_id' => $customField->id,
                            ],
                            [
                                'value' => is_array($fieldValue) ? implode(',', $fieldValue) : $fieldValue,
                            ]
                        );
                    }
                }
            }
    
            DB::commit();
            return redirect()->route('products.edit', $product->id)->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            // Delete additional field values
            $product->customFieldValues()->delete();

            // Delete related images
            $product->images()->delete();

            // Delete related product store records
            $product->productStores()->delete();

            // Delete the product itself
            $product->delete();

            DB::commit();
            return back()->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }


}