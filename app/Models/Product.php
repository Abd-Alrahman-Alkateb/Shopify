<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory;
    protected $fillable =['name','price','quantity','exp_date','featured_image','category_id','description','current_price','user_id', 'contact_info'];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function discounts()
    {
        return $this->hasMany(Discount::class,'product_id')->orderBy('date');
    }


}
