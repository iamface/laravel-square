<?php

namespace Iamface\LaravelSquare;

class LaravelSquareError
{
    public static function throwError($error, $status)
    {
        return response()->json(['error' => $error], $status);
    }
}
