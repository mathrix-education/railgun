<?php
namespace App\Http\Controllers;


use App\Models\Chapter;
use App\Models\Country;
use App\Models\Discipline;

class ChapterController extends BaseController
{
    public function index(Country $country, string $classShortSlug, Discipline $discipline) {
        $class = $country->classes->where('slug_short',  $classShortSlug)->first();

        $chapters = Chapter::all()
            ->where('class_id', $class->id)
            ->where('discipline_id', $discipline->id);

        dd($chapters);
    }
}