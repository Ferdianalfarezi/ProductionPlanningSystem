<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_mesin' => Mesin::count(),
            'total_users' => User::count(),
        ];

        return view('dashboard', compact('stats'));
    }
}