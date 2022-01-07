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

        if ($request->filled('sort')) {
            if ($request->filled('order')) {
                if ($request->order == 'ascending') {
                    if ($request->sort == 'name') {
                        $products = Product::orderBy('name', 'asc');
                    }
                    if ($request->sort == 'price') {
                        $products = Product::orderBy('price', 'asc');
                    }
                    if ($request->sort == 'views') {
                        $products = Product::orderBy('views', 'asc');
                    }
                    if ($request->sort == 'exp_date') {
                        $products = Product::orderBy('exp_date', 'asc');
                    }
                    if ($request->sort == 'creation_date') {
                        $products = Product::orderBy('created_at', 'asc');
                    }
                }
                if ($request->order == 'descending') {
                    if ($request->sort == 'name') {
                        $products = Product::orderBy('name', 'desc');
                    }
                    if ($request->sort == 'price') {
                        $products = Product::orderBy('price', 'desc');
                    }
                    if ($request->sort == 'views') {
                        $products = Product::orderBy('views', 'desc');
                    }
                    if ($request->sort == 'exp_date') {
                        $products = Product::orderBy('exp_date', 'desc');
                    }
                    if ($request->sort == 'creation_date') {
                        $products = Product::orderBy('created_at', 'desc');
                    }
                }
            }
            else {
                if ($request->sort == 'name') {
                    $products = Product::orderBy('name', 'asc');
                }
                if ($request->sort == 'price') {
                    $products = Product::orderBy('price', 'asc');
                }
                if ($request->sort == 'views') {
                    $products = Product::orderBy('views', 'asc');
                }
                if ($request->sort == 'exp_date') {
                    $products = Product::orderBy('exp_date', 'asc');
                }
                if ($request->sort == 'creation_date') {
                    $products = Product::orderBy('created_at', 'asc');
                }
            }
        }

        $products = $products->paginate(8);
        foreach ($products as $product) {
            $discounts =$product->discounts()->orderBy('date')->get();
            $max=null;
            foreach($discounts as $discount){
                if($discount['date']<=now()){
                    $max=$discount;
                }
            }
            if($max==null){
                $product['current_price']=$product->price;
            }
            if(!is_null($max)){
                $new_value = ($product->price*$max['discount_percentage'])/100;
                $product['current_price'] = $product->price - $new_value;
            }
        }

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
            'contact_info'   => 'required',
            'category_id'    => 'required|numeric|exists:categories,id',
            'date1'    =>   'require|date',
            'discount_percent1'    =>  'required|numeric',
            'date2'   =>   'require|date',
            'discount_percent2'    =>  'required|numeric',
            'date3'    =>   'require|date',
            'discount_percent3'    =>  'required|numeric',
        ]);

        $validation['featured_image'] =$request->featured_image->store('public/images');
        $validation['featured_image']=str_replace('public','/storage',$validation['featured_image']);

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
    public function show($product_id)
    {
        $product = Product::findOrFail($product_id);
        $discounts =$product->discounts()->orderBy('date')->get();
        $max=null;
        foreach($discounts as $discount){
            if($discount['date']<=now()){
                $max=$discount;
            }
        }
        if($max==null){
            $product['current_price']=$product->price;
        }
        if(!is_null($max)){
            $new_value = ($product->price*$max['discount_percentage'])/100;
            $product['current_price'] = $product->price - $new_value;
        }

        $product->increment('views');
        return new ProductResource(['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $product_id)
    {
        $product = Product::findOrFail($product_id);
        if ($product->user_id == Auth::id()) {
        $validation = $request->validate([
            'name'    => 'required|min:2|max:15',
            'price'   => 'required|numeric',
            'quantity'   => 'required|numeric',
            'featured_image'    => 'required|file|image',
            'description'   => 'required',
            'contact_info'   => 'required',
            'category_id'    => 'required|numeric|exists:categories,id',
            'date1'    =>   'require|date',
            'discount_percent1'    =>  'required|numeric',
            'date2'   =>   'require|date',
            'discount_percent2'    =>  'required|numeric',
            'date3'    =>   'require|date',
            'discount_percent3'    =>  'required|numeric',
        ]);

        $product->name = $validation['name'];
        $product->price = $validation['price'];
        $product->quantity = $validation['quantity'];
        $product->description = $validation['description'];
        $product->contact_info = $validation['contact_info'];
        $product->category_id = $validation['category_id'];
        $validation['featured_image'] =$request->featured_image->store('public/images');
        $product->featured_image = str_replace('public','/storage',$validation['featured_image']);

        $product->discounts()->orderBy('date')->delete();

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
    public function destroy($product_id)
    {
        $product = Product::findOrFail($product_id);
        if ($product->user_id == Auth::id()) {
        $product->discounts()->orderBy('date')->delete();

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
            $products->orwhere('contact_info', 'like', "%$request->search%");

            $products = $products->paginate(16);
            foreach ($products as $product) {
                $discounts =$product->discounts()->orderBy('date')->get();
                $max=null;
                foreach($discounts as $discount){
                    if($discount['date']<=now()){
                        $max=$discount;
                    }
                }
                if($max==null){
                    $product['current_price']=$product->price;
                }
                if(!is_null($max)){
                    $new_value = ($product->price*$max['discount_percentage'])/100;
                    $product['current_price'] = $product->price - $new_value;
                }
            }

            return ProductResource::collection($products);
        }
    }

    public function myProducts()
    {
        $products = Product::latest();
        $products->where('user_id', 'like', Auth::id());
        $products = $products->paginate(8);

        foreach ($products as $product) {
            $discounts =$product->discounts()->orderBy('date')->get();
            $max=null;
            foreach($discounts as $discount){
                if($discount['date']<=now()){
                    $max=$discount;
                }
            }
            if($max==null){
                $product['current_price']=$product->price;
            }
            if(!is_null($max)){
                $new_value = ($product->price*$max['discount_percentage'])/100;
                $product['current_price'] = $product->price - $new_value;
            }
        }

        return ProductResource::collection($products);
    }
}
