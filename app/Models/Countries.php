<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Countries extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'countries';
    protected $primaryKey = 'countries_id';
    public $timestamps = false;

    protected $fillable = [
        'shortname',
        'name',
        'phonecode',
        
    ]; 
}