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
        // добавь сюда все поля, которые хочешь массово заполнять
    ];
}
