<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'categories'    => 'array',
            'categories.*'  => 'numeric'
        ]);

        $products = Product::latest();

        if ($request->filled('q')) {
            $products->where('name', 'like', "%$request->q%");
            $products->orwhere('price', 'like', "%$request->q%");
            $products->orwhere('quantity', 'like', "%$request->q%");
            $products->orwhere('exp_date', 'like', "%$request->q%");
            $products->orwhere('description', 'like', "%$request->q%");
        }
        if ($request->filled('categories')) {
            $products->whereIn('category_id', $request->category);
        }

        $products = $products->paginate(8);
        $categories = Category::all();
        return ProductResource::collection(['products' => $products,'categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = $request->validate([
            'name'    => 'required',
            'price'   => 'required|numeric',
            'quantity'   => 'required|numeric',
            'exp_date'    => 'required|date',
            'featured_image'    => 'required',
            'featured_image.*'    => 'required|file|image',
            'description'   => 'required',
            'category_id'    => 'required|numeric|exists:categories,id',
        ]);
        foreach ($validation['featured_image'] as $featured_image) {
            $featured_image->store('public/images');
        }
        $validation['user_id'] = Auth::id();
        $product = Product::create($validation);
        return response(['message' => 'product was created']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return new ProductResource(['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id == Auth::id()) {
        $validation = $request->validate([
            'name'    => 'required',
            'price'   => 'required|numeric',
            'quantity'   => 'required|numeric',
            'featured_image'    => 'required|array',
            'featured_image.*'    => 'required|file|image',
            'description'   => 'required',
            'category_id'    => 'required|numeric|exists:categories,id',
        ]);

        $product->name = $validation['name'];
        $product->price = $validation['price'];
        $product->quantity = $validation['quantity'];
        $product->featured_image = $validation['featured_image'];
        $product->description = $validation['description'];
        $product->category_id = $validation['category_id'];
        foreach ($validation['featured_image'] as $featured_image) {
            $featured_image->store('public/images');
        }

        $product->save();
        return response(['message' => 'product was edited']);
    }
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if ($product->user_id == Auth::id()) {
            $product->delete();
            return response(['message' => 'product successfully deleted!']);
        }
    }
}
