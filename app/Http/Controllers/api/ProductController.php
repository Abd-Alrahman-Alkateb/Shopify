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
            'category'    => 'numeric',
        ]);

        $products = Product::latest();

        if ($request->filled('search')) {
            $products->where('name', 'like', "%$request->search%");
            $products->orwhere('price', 'like', "%$request->search%");
            $products->orwhere('quantity', 'like', "%$request->search%");
            $products->orwhere('exp_date', 'like', "%$request->search%");
            $products->orwhere('description', 'like', "%$request->search%");
        }
        if ($request->filled('category')) {
            $products->where('category_id', 'like', "$request->category");
        }

        $products = $products->paginate(8);
        $categories = Category::all();
        return ProductResource::collection($products);
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
            'name'    => 'required|min:2|max:15',
            'price'   => 'required|numeric',
            'quantity'   => 'required|numeric',
            'exp_date'    => 'required|date',
            'featured_image'    => 'required|file|image',
            'description'   => 'required',
            'category_id'    => 'required|numeric|exists:categories,id',
            'date1'    =>   'required',
            'discount_percent1'    =>  'required',
            'date2'   =>   'required',
            'discount_percent2'    =>  'required',
            'date3'    =>   'required',
            'discount_percent3'    =>  'required',
        ]);

        $validation['featured_image'] = $request->featured_image->store('public/images');
        $validation['user_id'] = Auth::id();
        $product = Product::create($validation);
        $product->discounts()->create([
            'date' => $validation['date1'],
            'discount_percentage' => $validation['discount_percent1']
        ]);
        $product->discounts()->create([
            'date' => $validation['date2'],
            'discount_percentage' => $validation['discount_percent2']
        ]);
        $product->discounts()->create([
            'date' => $validation['date3'],
            'discount_percentage' => $validation['discount_percent3']
        ]);

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
        $discounts =$product->discounts()->orderBy('date')->get();
        $max=null;
        foreach($discounts as $discount){
            if($discount['date']<=now()){
                $max=$discount;
            }
        }
        if(!is_null($max)){
            $new_value = ($product->price*$max['discount_percentage'])/100;
            $product['current_price'] = $product->price - $new_value;
        }


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
            'name'    => 'required|min:2|max:15',
            'price'   => 'required|numeric',
            'quantity'   => 'required|numeric',
            'featured_image'    => 'required|file|image',
            'description'   => 'required',
            'category_id'    => 'required|numeric|exists:categories,id',
        ]);

        $product->name = $validation['name'];
        $product->price = $validation['price'];
        $product->quantity = $validation['quantity'];
        $product->featured_image = $validation['featured_image'];
        $product->description = $validation['description'];
        $product->category_id = $validation['category_id'];
        $product->featured_image = $request->featured_image->store('public/images');


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

    public function search(Request $request)
    {
        $products = Product::latest();

        if ($request->filled('search')) {
            $products->where('name', 'like', "%$request->search%");
            $products->orwhere('price', 'like', "%$request->search%");
            $products->orwhere('quantity', 'like', "%$request->search%");
            $products->orwhere('exp_date', 'like', "%$request->search%");
            $products->orwhere('description', 'like', "%$request->search%");

            $products = $products->paginate(16);
            return ProductResource::collection($products);
        }
    }

    public function myProducts()
    {
        $products = Product::latest();
        $products->where('user_id', 'like', Auth::id());
        $products = $products->paginate(8);

        return ProductResource::collection($products);
    }
}
