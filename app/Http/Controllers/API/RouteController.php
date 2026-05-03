<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Http;
use App\Models\Line;
use App\Models\Schedule;

class RouteController extends Controller
{
    public function search(Request $request)
    {
        $from = trim($request->get('from', ''));
        $to = trim($request->get('to', ''));

        if (!$from || !$to) {
            return response()->json([]);
        }

        $lines = Line::with(['stops', 'schedules'])->get()
            ->filter(function ($line) use ($from, $to) {

                $fromStop = $line->stops
                    ->filter(function ($stop) use ($from) {
                        return stripos($stop->name, $from) !== false;
                    })
                    ->sortBy('order')
                    ->first();

                $toStop = $line->stops
                    ->filter(function ($stop) use ($to) {
                        return stripos($stop->name, $to) !== false;
                    })
                    ->sortBy('order')
                    ->first();

                if (!$fromStop || !$toStop) {
                    return false;
                }

                return $fromStop->order < $toStop->order;
            })
            ->values();

        return response()->json($lines);
    }

    public function nextBus($line_id)
    {
        $now = now()->format('H:i:s');

        $next = Schedule::where('line_id', $line_id)
            ->where('departure_time', '>', $now)
            ->orderBy('departure_time')
            ->first();

        return response()->json($next);
    }

    public function routePath(Request $request)
    {
        $data = $request->validate([
            'stops' => 'required|array|min:2',
            'stops.*.latitude' => 'required|numeric',
            'stops.*.longitude' => 'required|numeric',
        ]);

        $route = $this->getRouteFromRoutingServer($data['stops']);

        return response()->json($route);
    }

    private function getRouteFromRoutingServer(array $stops): array
    {
        $baseUrl = rtrim(config('services.routing.url'), '/');

        $coordinates = collect($stops)
            ->map(function ($stop) {
                return $stop['longitude'] . ',' . $stop['latitude'];
            })
            ->implode(';');

        $url = "{$baseUrl}/route/v1/driving/{$coordinates}";

        try {
            $response = Http::timeout(10)->get($url, [
                'overview' => 'full',
                'geometries' => 'geojson',
                'alternatives' => 'false',
                'steps' => 'false',
            ]);

            if (!$response->successful()) {
                throw new Exception("Serveur OSRM indisponible");
            }

            $data = $response->json();

            if (
                !isset($data['routes']) ||
                !is_array($data['routes']) ||
                count($data['routes']) === 0
            ) {
                throw new Exception("Aucun trajet trouvé");
            }

            $route = $data['routes'][0];
            $coordinates = $route['geometry']['coordinates'] ?? [];

            return [
                'distance_km' => round(((float) $route['distance']) / 1000, 2),
                'duration_min' => round(((float) $route['duration']) / 60, 2),
                'coordinates' => array_map(function ($point) {
                    return [(float) $point[1], (float) $point[0]]; // Leaflet = [lat, lng]
                }, $coordinates),
            ];
        } catch (Throwable $e) {
            return [
                'distance_km' => 0,
                'duration_min' => 0,
                'coordinates' => collect($stops)
                    ->map(function ($stop) {
                        return [
                            (float) $stop['latitude'],
                            (float) $stop['longitude'],
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        }
    }

    public function userToStopRoute(Request $request)
    {
        $data = $request->validate([
            'user_latitude' => 'required|numeric',
            'user_longitude' => 'required|numeric',
            'stop_latitude' => 'required|numeric',
            'stop_longitude' => 'required|numeric',
        ]);

        $route = $this->getRouteFromRoutingServerSimple(
            (float) $data['user_latitude'],
            (float) $data['user_longitude'],
            (float) $data['stop_latitude'],
            (float) $data['stop_longitude']
        );

        return response()->json($route);
    }

    private function getRouteFromRoutingServerSimple(
        float $pickupLat,
        float $pickupLng,
        float $deliveryLat,
        float $deliveryLng
    ): array {
        $baseUrl = rtrim(config('services.routing.url'), '/');

        // OSRM attend : longitude,latitude
        $url = "{$baseUrl}/route/v1/driving/{$pickupLng},{$pickupLat};{$deliveryLng},{$deliveryLat}";

        try {
            $response = Http::timeout(5)->get($url, [
                'overview' => 'full',
                'geometries' => 'geojson',
                'alternatives' => 'false',
                'steps' => 'false',
            ]);

            if (!$response->successful()) {
                throw new \Exception("Serveur de route indisponible");
            }

            $data = $response->json();

            if (
                !isset($data['routes']) ||
                !is_array($data['routes']) ||
                count($data['routes']) === 0
            ) {
                throw new \Exception("Aucun trajet trouvé");
            }

            $route = $data['routes'][0];
            $coordinates = $route['geometry']['coordinates'] ?? [];

            return [
                'distance_km' => round(((float) $route['distance']) / 1000, 2),
                'duration_min' => round(((float) $route['duration']) / 60, 2),
                'coordinates' => array_map(function ($point) {
                    return [(float) $point[1], (float) $point[0]];
                }, $coordinates),
            ];
        } catch (\Throwable $e) {
            $distanceKm = $this->calculateDistanceKm(
                $pickupLat,
                $pickupLng,
                $deliveryLat,
                $deliveryLng
            );

            return [
                'distance_km' => round($distanceKm, 2),
                'duration_min' => $this->estimateDurationFromDistance($distanceKm),
                'coordinates' => [
                    [$pickupLat, $pickupLng],
                    [$deliveryLat, $deliveryLng],
                ],
            ];
        }
    }
}