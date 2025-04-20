<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function receiveStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'comment' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($validated['product_id']);

            // Create stock movement
            $movement = StockMovement::create([
                'product_id' => $validated['product_id'],
                'type' => 'received',
                'quantity' => $validated['quantity'],
                'comment' => $validated['comment'] ?? null,
                'user_id' => Auth::id()
            ]);

            // Update product stock quantity
            $product->quantity_in_stock += $validated['quantity'];
            $product->save();

            DB::commit();

            return response()->json([
                'message' => 'Stock received successfully',
                'product' => $product,
                'movement' => $movement
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while receiving stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function shipStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'comment' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($validated['product_id']);

            // Check if there's enough stock
            if ($product->quantity_in_stock < $validated['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock for this product'
                ], 400);
            }

            // Create stock movement
            $movement = StockMovement::create([
                'product_id' => $validated['product_id'],
                'type' => 'shipped',
                'quantity' => $validated['quantity'],
                'comment' => $validated['comment'] ?? null,
                'user_id' => Auth::id()
            ]);

            // Update product stock quantity
            $product->quantity_in_stock -= $validated['quantity'];
            $product->save();

            DB::commit();

            return response()->json([
                'message' => 'Stock shipped successfully',
                'product' => $product,
                'movement' => $movement
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while shipping stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stockHistory(Request $request)
    {
        $query = StockMovement::with(['product', 'user']);

        // Filters
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json(['data' => $movements], 200);
    }

    public function inventory()
    {
        $products = Product::with(['category', 'supplier'])
            ->orderBy('category_id')
            ->get();

        $totalValue = $products->sum(function ($product) {
            return $product->purchase_price * $product->quantity_in_stock;
        });

        return response()->json([
            'products' => $products,
            'total_value' => $totalValue
        ], 200);
    }

    public function stockAlerts()
    {
        $alerts = Product::where('quantity_in_stock', '<=', \DB::raw('minimum_stock'))
            ->with(['category', 'supplier'])
            ->get();

        return response()->json(['data' => $alerts], 200);
    }
}
