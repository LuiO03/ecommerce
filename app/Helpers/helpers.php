<?php

use Carbon\Carbon;

if (!function_exists('fecha_hoy')) {
    function fecha_hoy()
    {
        Carbon::setLocale('es');
        return ucfirst(Carbon::now()->isoFormat('dddd, D [de] MMMM [de] YYYY'));
    }
}
