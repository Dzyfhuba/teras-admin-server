<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StudentsController extends Controller
{
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx',
        ]);

        if ($validator->fails()) {
            return response([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        Excel::import(new StudentsImport, $request->file('file'));

        return response([
            'message' => 'Import successful',
        ]);
    }
}
