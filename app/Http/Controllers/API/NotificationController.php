<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(Notification::all());
    }

    public function show($id)
    {
        return response()->json(Notification::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        $notification = Notification::create($data);

        return response()->json($notification, 201);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        $data = $request->validate([
            'message' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
        ]);

        $notification->update($data);

        return response()->json([
            'message' => 'Notification mise à jour',
            'notification' => $notification
        ]);
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(null, 204);
    }
}