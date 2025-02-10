<?php

namespace App\Http\Controllers\api;

use App\Models\Service;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class PriceHistoryController extends Controller
{
    /**
     * Display price history for a service
     *
     * @param Service $service
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Service $service, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid date range',
                'errors' => $validator->errors()
            ], 422);
        }

        $history = $service->getPriceHistory(
            $request->input('start_date'),
            $request->input('end_date')
        )->map(function ($record) {
            return [
                'id' => $record->id,
                'date' => $record->created_at->format('Y-m-d H:i:s'),
                'old_price' => number_format($record->old_price, 2),
                'new_price' => number_format($record->new_price, 2),
                'change' => number_format($record->new_price - $record->old_price, 2),
                'change_percentage' => round((($record->new_price - $record->old_price) / $record->old_price) * 100, 2),
                'reason' => $record->reason,
                'changed_by' => [
                    'id' => $record->user->id,
                    'name' => $record->user->name
                ]
            ];
        });

        return response()->json([
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'current_price' => number_format($service->getCurrentPrice(), 2)
            ],
            'history' => $history
        ]);
    }

    /**
     * Update service price and create history record
     *
     * @param Service $service
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePrice(Service $service, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_price' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $newPrice = (float) $request->input('new_price');
        $reason = $request->input('reason');

        $updated = $service->updatePrice($newPrice, $reason);

        if (!$updated) {
            return response()->json([
                'message' => 'Price is the same as current price. No update needed.'
            ], 422);
        }

        return response()->json([
            'message' => 'Price updated successfully',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'current_price' => number_format($service->getCurrentPrice(), 2)
            ]
        ]);
    }

    /**
     * Get price change statistics for a service
     *
     * @param Service $service
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatistics(Service $service, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|in:day,week,month,year',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate && $request->input('period')) {
            $startDate = Carbon::now()->sub($request->input('period'))->startOfDay();
            $endDate = Carbon::now();
        }

        $history = $service->getPriceHistory($startDate, $endDate);

        $statistics = [
            'total_changes' => $history->count(),
            'initial_price' => $history->last()?->old_price ?? $service->getCurrentPrice(),
            'final_price' => $service->getCurrentPrice(),
            'biggest_increase' => $history->where('new_price', '>', 'old_price')
                ->sortByDesc(function ($record) {
                    return $record->new_price - $record->old_price;
                })->first(),
            'biggest_decrease' => $history->where('new_price', '<', 'old_price')
                ->sortBy(function ($record) {
                    return $record->new_price - $record->old_price;
                })->first(),
            'average_change' => $history->average(function ($record) {
                return $record->new_price - $record->old_price;
            })
        ];

        return response()->json([
            'service' => [
                'id' => $service->id,
                'name' => $service->name
            ],
            'period' => [
                'start' => $startDate ? Carbon::parse($startDate)->format('Y-m-d') : null,
                'end' => $endDate ? Carbon::parse($endDate)->format('Y-m-d') : null
            ],
            'statistics' => $statistics
        ]);
    }

    /**
     * Delete a price history record (if allowed)
     *
     * @param Service $service
     * @param PriceHistory $priceHistory
     * @return JsonResponse
     */
    public function destroy(Service $service, PriceHistory $priceHistory): JsonResponse
    {
        // Ensure the price history belongs to the service
        if ($priceHistory->service_id !== $service->id) {
            return response()->json([
                'message' => 'Price history record does not belong to this service'
            ], 403);
        }

        // Only allow deletion of the most recent price change
        $latestHistory = $service->priceHistories()->latest()->first();
        if ($priceHistory->id !== $latestHistory->id) {
            return response()->json([
                'message' => 'Only the most recent price change can be deleted'
            ], 403);
        }

        // Revert the service price to the previous price
        $service->price = $priceHistory->old_price;
        $service->save();

        $priceHistory->delete();

        return response()->json([
            'message' => 'Price history record deleted and price reverted successfully',
            'current_price' => number_format($service->getCurrentPrice(), 2)
        ]);
    }
}
