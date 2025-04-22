<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard with user listing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Instead of making an HTTP request to our own API, fetch users directly from the database
        $users = [
            'data' => User::with('roles')->paginate(10)->items(),
            'meta' => [
                'current_page' => User::paginate(10)->currentPage(),
                'last_page' => User::paginate(10)->lastPage(),
                'per_page' => User::paginate(10)->perPage(),
                'total' => User::paginate(10)->total(),
            ]
        ];
        
        return view('dashboard', compact('users'));
    }
}