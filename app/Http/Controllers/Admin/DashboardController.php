<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Event;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEvents = Event::count();
        $totalRegistrations = DB::table('tbl_registration')->count();
        $totalMessages = Contact::count();

        $recentRegistrations = DB::table('tbl_registration')
            ->latest()
            ->take(6)
            ->get();

        $recentMessages = Contact::latest()->take(6)->get(); // ✅ Add this line

        return view('admin.dashboard', compact(
            'totalEvents',
            'totalRegistrations',
            'totalMessages',
            'recentRegistrations',
            'recentMessages' // ✅ Include this
        ));
    }
}
