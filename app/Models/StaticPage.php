<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    protected $table = 'static_pages';
    protected $primaryKey = 'static_page_id';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'status',
    ];
}
