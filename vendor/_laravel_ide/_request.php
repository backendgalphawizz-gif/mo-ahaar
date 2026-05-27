<?php

namespace Illuminate\Http;

interface Request
{
    /**
     * @return \App\Models\Users|null
     */
    public function user($guard = null);
}