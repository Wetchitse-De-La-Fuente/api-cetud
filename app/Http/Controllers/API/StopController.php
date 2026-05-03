<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stop;

class StopController extends Controller
{
    public function index()
    {
        return response()->json(Stop::with('line')->get());
    }

    public function show($id)
    {
        return response()->json(Stop::with('line')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'line_id' => 'required|exists:lines,id',
            'order' => 'required|integer|min:1',
        ]);

        $stop = Stop::create($data);

        return response()->json($stop, 201);
    }

    public function update(Request $request, $id)
    {
        $stop = Stop::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'line_id' => 'sometimes|exists:lines,id',
            'order' => 'sometimes|integer|min:1',
        ]);

        $stop->update($data);

        return response()->json([
            'message' => 'Arrêt mis à jour',
            'stop' => $stop
        ]);
    }

    public function destroy($id)
    {
        $stop = Stop::findOrFail($id);
        $stop->delete();

        return response()->json(null, 204);
    }
}