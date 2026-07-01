<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request) {
        $sortBy = $request->query('sort_by', 'id');
        $sortDir = $request->query('sort_dir', 'asc');

        // Validate to prevent SQL injection
        $sortBy = in_array($sortBy, ['id']) ? $sortBy : 'id';
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $customers = User::where('is_admin', false)
            ->withCount('orders')
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer) {
        $customer->load('orders.items');
        return view('admin.customers.show', compact('customer'));
    }

    public function destroy(User $customer) {
        if ($customer->is_admin) {
            return back()->with('error', 'Cannot delete admin users.');
        }

        // Delete customer's orders first
        $customer->orders()->delete();

        // Delete the customer
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully.');
    }
}
