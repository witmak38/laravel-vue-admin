<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'content',
        'meta_title',
        'meta_description',
        // не реализовано
        //'status' => ['nullable', 'in:draft,published,archived'],
        // добавь сюда все поля, которые хочешь массово заполнять
    ];

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('sort_order');
    }
    public function checkImageRelation()
    {
        return [
            'relation_exists' => method_exists($this, 'images'),
            'is_morph_many' => $this->images() instanceof \Illuminate\Database\Eloquent\Relations\MorphMany,
            'images_count' => $this->images()->count(),
            'images_sql' => $this->images()->toSql(),
            'loaded_images' => $this->relationLoaded('images') ? $this->images : null
        ];
    }


}
