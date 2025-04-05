<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $recentOrders = auth()->user()->orders()->latest()->take(5)->get();
        return view('customer.dashboard', compact('recentOrders'));
    }
}
