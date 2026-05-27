<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usersrole extends Model
{
    use HasFactory;

    protected $table = 'users_role';

    protected $primaryKey = 'role_type';

    public $timestamps = false;

    protected $fillable = [
        'role_type',
        'role'
    ];
}