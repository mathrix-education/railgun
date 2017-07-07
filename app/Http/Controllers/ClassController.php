<?php

namespace App\Http\Controllers;


use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;

class ClassController extends BaseController
{
    public function cursus(Discipline $discipline, Country $country) {
        //$classes = Class_::where()
        return response()->json();
    }
}