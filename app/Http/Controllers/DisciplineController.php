<?php
namespace App\Http\Controllers;

use App\Models\Class_;

/**
 * Class DisciplineController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class DisciplineController extends BaseController
{
    /**
     * Retrieve disciplines based on class
     *
     * @param Class_ $class
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Class_ $class)
    {
        return response()->json($class->disciplines);
    }
}
