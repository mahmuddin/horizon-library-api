<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'authors';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'bio',
        'profile_image',
        'social_media',
        'nationality',
        'birth_date',
        'categories'
    ];

    protected $casts = [
        'social_media' => 'json',
        'categories' => 'array',
    ];
}
