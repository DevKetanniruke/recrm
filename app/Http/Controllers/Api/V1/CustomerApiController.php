<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $customers = Customer::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return CustomerResource::collection($customers);
    }

    public function show(Customer $customer)
    {
        if ($customer->company_id !== auth()->user()->company_id) {
            return response()->json(['message' => 'Unauthorized access to customer resource.'], 403);
        }

        return new CustomerResource($customer);
    }
}
