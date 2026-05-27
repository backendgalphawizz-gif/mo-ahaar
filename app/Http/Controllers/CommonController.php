<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Countries;
class CommonController extends Controller
{


    public function getStateList($country_id)
    {
        $states = DB::table('states')->where('country_id', $country_id)->get();
        return response()->json($states);
    }  
    public function getCityList($state_id)
    {
        $cities = DB::table('cities')->where('state_id', $state_id)->get();
        return response()->json($cities);
    } 
    
    public function getSubCategory($category_id)
    {
        // Treat NULL status as active: SQL `status != 0` excludes rows where status IS NULL.
        $subCategories = DB::table('sub_categories')
            ->where('category_id', $category_id)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereNotIn('status', [0, '0']);
            })
            ->orderBy('sub_cat_name')
            ->get();

        return response()->json($subCategories);
    }
}