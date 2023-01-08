<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function variant(){
        return $this->hasMany(ProductVariant::class);
    }
    public function productVariantPrice(){
        return $this->hasMany(ProductVariantPrice::class);
    }

    public function productVariant($id){
       return  ProductVariant::where('product_id', $id)->select('variant')->get();
    }

    public function productPrice($id){
        return ProductVariantPrice::where('product_id', $id)->select('price','stock')->first();
    }



}
