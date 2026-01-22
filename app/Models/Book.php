<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'title',
        'author',
        'published_at',
        'stock',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loan()
    {
        return $this->hasMany(Loan::class);
    }
}
