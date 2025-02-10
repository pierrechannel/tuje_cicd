<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Dept;
use Illuminate\Support\Facades\Validator;



class CustomerController extends Controller
{


        public function index()
        {
            return response()->json(Customer::all());
        }

        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'email' => 'required|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $customer = Customer::create($request->all());
            return response()->json($customer, 201);
        }

        public function show($id)
        {
            $customer = Customer::findOrFail($id);
            return response()->json($customer);
        }

        public function update(Request $request, $id)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|required|string|max:15',
                'email' => 'sometimes|required|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $customer = Customer::findOrFail($id);
            $customer->update($request->all());
            return response()->json($customer);
        }

        public function destroy($id)
        {
            $customer = Customer::findOrFail($id);
            $customer->delete();
            return response()->json(['message' => 'Customer deleted successfully.']);
        }


    }
