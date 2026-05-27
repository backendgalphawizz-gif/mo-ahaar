<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;

class StaticPageController extends Controller
{
    public function show($slug)
    {
        $page = StaticPage::where('slug', $slug)->where('status', 1)->firstOrFail();

        return view('static-pages.show', compact('page'));
    }
}
