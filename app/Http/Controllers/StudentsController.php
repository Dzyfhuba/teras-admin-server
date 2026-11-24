<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['message' => 'Success', 'data' => []]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Success', 'data' => null], 201);
    }

    public function show(string $id)
    {
        return response()->json(['message' => 'Success', 'data' => null]);
    }

    public function update(Request $request, string $id)
    {
        return response()->json(['message' => 'Success', 'data' => null]);
    }

    public function destroy(string $id)
    {
        return response()->json(['message' => 'Success', 'data' => null]);
    }

    public function import(Request $request)
    {
        return response()->json(['message' => 'Success', 'data' => null]);
    }

    public function sections(string $student)
    {
        return response()->json(['message' => 'Success', 'data' => []]);
    }

    public function attachSection(Request $request, string $student)
    {
        return response()->json(['message' => 'Success', 'data' => null], 201);
    }
}
