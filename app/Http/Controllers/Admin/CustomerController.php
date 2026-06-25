<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index() {
        $customers = User::where('is_admin', false)
            ->withCount('orders')
            ->latest()
            ->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer) {
        $customer->load('orders');
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
