<?php

namespace App\Actions;

// use Illuminate\Support\Str;
use Str;

class StrRandom
{
    public function get($length)
    {
        // return \Str::random($length);
        return Str::random($length);
    }
}
