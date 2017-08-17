<?php
namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class ModuleController
 * @package App\Http\Controllers
 */
class ModuleController extends BaseController {
    public function get(Module $module) {
        return $module;
    }

    /**
     * @apiVerson 1.0.0
     * @apiGroup Modules
     * @
     * @param Request $request
     */
    public function add(Request $request) {

    }
}