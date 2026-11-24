<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SectionController extends Controller
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

    public function students(string $section)
    {
        return response()->json(['message' => 'Success', 'data' => []]);
    }

    public function attachStudents(Request $request, string $section)
    {
        return response()->json(['message' => 'Success', 'data' => null], 201);
    }

    public function detachStudent(string $section, string $student)
    {
        return response()->json(['message' => 'Success', 'data' => null]);
    }
}
