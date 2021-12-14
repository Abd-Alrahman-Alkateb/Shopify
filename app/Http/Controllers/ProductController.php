<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

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

        return view('products.index', ['products' => $products,'categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('products.create',['categories' => $categories]);
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
            'featured_image'    => 'required|file|image',
            'description'   => 'required',
            'category_id'    => 'required|numeric|exists:category,id',
        ]);

        $validation['featured_image'] = $request->featured_image->store('public/images');
        $product = Product::create($validation);
        return redirect()->route('products.index');


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('products.show', ['product' => $product]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', ['product' => $product,'categories' => $categories]);
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
        $validation = $request->validate([
            'name'    => 'required',
            'price'   => 'required|numeric',
            'quantity'   => 'required|numeric',
            'featured_image'    => 'required|file|image',
            'description'   => 'required',
            'category_id'    => 'required|numeric|exists:category,id',
        ]);

        $product->name = $validation->name;
        $product->price = $validation->price;
        $product->quantity = $validation->quantity;
        $product->featured_image = $request->featured_image->store('public/images');
        $product->description = $validation->description;
        $product->category_id = $validation->category_id;

        $product->save();
        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
