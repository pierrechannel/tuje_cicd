<?php

namespace App\Http\Controllers\api;
use App\http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $services = Service::all();
        return response()->json($services);
    }

    /**
     * Store a newly created service in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'type' => 'required|string|max:255',
        ]);

        $service = Service::create($validatedData);

        return response()->json($service, 201);
    }

    /**
     * Display the specified service.
     *
     * @param Service $service
     * @return JsonResponse
     */
    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    /**
     * Update the specified service in storage.
     *
     * @param Request $request
     * @param Service $service
     * @return JsonResponse
     */
    public function update(Request $request, Service $service): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'type' => 'sometimes|required|string|max:255',
        ]);

        $service->update($validatedData);

        return response()->json($service);
    }

    /**
     * Update the price of the specified service.
     *
     * @param Request $request
     * @param Service $service
     * @return JsonResponse
     */
    public function updatePrice(Request $request, Service $service): JsonResponse
    {
        $validatedData = $request->validate([
            'new_price' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
        ]);

        $updated = $service->updatePrice($validatedData['new_price'], $validatedData['reason']);

        if ($updated) {
            return response()->json($service);
        }

        return response()->json(['message' => 'Price not changed.'], 400);
    }

    /**
     * Get the price history for the specified service.
     *
     * @param Service $service
     * @param Request $request
     * @return JsonResponse
     */
    public function priceHistory(Service $service, Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $history = $service->getPriceHistory($startDate, $endDate);

        return response()->json($history);
    }

    /**
     * Remove the specified service from storage.
     *
     * @param Service $service
     * @return JsonResponse
     */
    public function destroy(Service $service): JsonResponse
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully.']);
    }
}
