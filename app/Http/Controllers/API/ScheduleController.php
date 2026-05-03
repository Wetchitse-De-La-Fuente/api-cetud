<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    public function index()
    {
        return response()->json(Schedule::with(['line', 'stop'])->get());
    }

    public function show($id)
    {
        return response()->json(Schedule::with(['line', 'stop'])->findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'line_id' => 'required|exists:lines,id',
            'stop_id' => 'required|exists:stops,id',
            'departure_time' => 'required|date_format:H:i:s',
        ]);

        $schedule = Schedule::create($data);

        return response()->json($schedule, 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $data = $request->validate([
            'line_id' => 'sometimes|exists:lines,id',
            'stop_id' => 'sometimes|exists:stops,id',
            'departure_time' => 'sometimes|date_format:H:i:s',
        ]);

        $schedule->update($data);

        return response()->json([
            'message' => 'Horaire mis à jour',
            'schedule' => $schedule
        ]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json(null, 204);
    }
}