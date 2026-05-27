<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * @return \App\Models\Users|null
     */
    public function user();
}