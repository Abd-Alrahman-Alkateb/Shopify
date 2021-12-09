<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations;
    public $translatable =['name'];
    protected $fillable =['name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
