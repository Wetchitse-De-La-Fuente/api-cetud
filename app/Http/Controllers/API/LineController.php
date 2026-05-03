<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Line;

class LineController extends Controller
{
    public function index()
    {
        return response()->json(Line::with('stops','schedules')->get());
    }

    public function show($id)
    {
        return response()->json(Line::with('stops','schedules')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:lines,name',
            'start_point' => 'required|string|max:255',
            'end_point' => 'required|string|max:255',
        ]);

        $line = Line::create($data);

        return response()->json($line, 201);
    }

    public function update(Request $request, $id)
    {
        $line = Line::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255|unique:lines,name,' . $line->id,
            'start_point' => 'sometimes|string|max:255',
            'end_point' => 'sometimes|string|max:255',
        ]);

        $line->update($data);

        return response()->json([
            'message' => 'Ligne mise à jour',
            'line' => $line
        ]);
    }

    public function destroy($id)
    {
        $line = Line::findOrFail($id);
        $line->delete();

        return response()->json(null, 204);
    }
}