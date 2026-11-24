<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentsImportTemplateController extends Controller
{
    public function download(Request $request)
    {
        return response()->json(['message' => 'Success', 'data' => null]);
    }
}
