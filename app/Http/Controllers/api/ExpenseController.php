<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
//use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('user', 'category')->get(); // Load user and category relationships
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|numeric',
            'amount' => 'required|numeric',
            'description' => 'required|string|max:255',
            'category_id' => 'required|exists:expense_categories,id',
        ]);
        //$validatedData['user_id'] = 1; // Automatically set the authenticated user ID

        $expense = Expense::create($validatedData);
        return response()->json($expense, 201);
    }

    public function show($id)
    {
        $expense = Expense::with('category')->findOrFail($id);
        return response()->json($expense);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $validatedData = $request->validate([
            'amount' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:expense_categories,id',
        ]);

        $expense->update($validatedData);
        return response()->json($expense);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return response()->json(['message' => 'Expense deleted successfully']);
    }

    public function getCategories()
    {
        $categories = ExpenseCategory::all();
        return response()->json($categories);
    }
}
