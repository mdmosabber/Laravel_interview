<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        if($request){

            $title = $request->title;
            $variant = $request->variant;
            $price_from = $request->price_from;
            $price_to = $request->price_to;
            $date = $request->date;

            $products = Product::with('productVariantPrice')
                ->when($title, function ($query, $title) {
                    return $query->where('title', 'like', '%'.$title.'%');
                })
                ->when($date, function ($query, $date) {
                    return $query->whereDate('created_at', $date);
                })
                ->when($variant, function ($query, $variant) {
                    return $query->where('id', $variant);
                })
                ->when($price_from, function ($query, $price_from) {
                    return $query->where('price', $price_from);
                })
                ->when($price_to, function ($query, $price_to) {
                    return $query->where('price', $price_to);
                })
                ->paginate(5);

            $product_variants = ProductVariant::all();
            return view('products.index',compact('products','product_variants'));
        }

        $products = Product::latest()->paginate(5);
        $product_variants = ProductVariant::all();
        return view('products.index',compact('products','product_variants'));
    }


    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }


    public function store(Request $request)
    {

        if($request->isMethod('post')){

            $product                = new Product();
            $product->title         = $request->title;
            $product->sku           = $request->sku;
            $product->description   = $request->description;
            $product->save();



            $productImage               = new ProductImage();
            $productImage->product_id   = $product->id;

            foreach ($request->product_variant as $product_img){
                $imagefile  = $product_img->file('file');
                $imageName = time().'_'.$imagefile->getClientOriginalName();
                $directory = public_path('product-images/') ;
                $imagefile->move($directory, $imageName);
            }

            $productImage->file_path = $directory . $imageName;
            $productImage->save();


            $productVariant = new ProductVariant();
            foreach ($request->product_variant as $product_var){

                $decode_product_var = json_decode($product_var);

                foreach ($decode_product_var->tags as $tag){
                    $productVariant->variant  = $tag;
                    $productVariant->variant_id  = $product_var->option;
                    $productVariant->product_id  = $product->id;
                }
            }
            $productVariant->save();



            $productVariantPrice = new ProductVariantPrice();
            foreach ( $request->product_variant_prices as $product_var_pri){

                $product_var_pri = json_decode($product_var_pri);

                $productVariantPrice->price         = $product_var_pri->price;
                $productVariantPrice->stock         = $product_var_pri->stock;
                $productVariantPrice->product_id    = $product->id;
            }
            $productVariantPrice->save();


        }


    }


    public function show($product)
    {

    }

    public function edit(Product $product)
    {
        $product = Product::with(['productVariantPrice','variant'])->find($product->id);
        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product'));
    }




    public function update(Request $request, Product $product)
    {
        //
    }


    public function destroy(Product $product)
    {
        //
    }
}
