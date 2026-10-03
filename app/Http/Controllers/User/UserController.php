<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Admin\Event;
use App\Models\Admin\Category;
use App\Models\Admin\EventBeltFee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Registration;


class UserController extends Controller
{
    public function index()
    {
        $categories = Category::all(); // Or apply `->where('status', 'active')` if needed
        return view('user.index', compact('categories'));
       
    }

    public function about()
    {
        return view('user.about');
    }

    
    public function events(Request $request)
    {
        $categories = Category::all();
        $timeframe = $request->query('timeframe', 'all'); // 'all', 'upcoming', 'last'
        $categorySlug = $request->query('category', 'all');

        $query = Event::with('category')->where('is_active', true);

        // Filter by category if passed
        if (!empty($categorySlug) && $categorySlug !== 'all') {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->whereRaw("LOWER(REPLACE(name, ' ', '-')) = ?", [strtolower($categorySlug)])
                  ->orWhere('name', 'LIKE', '%' . str_replace('-', ' ', $categorySlug) . '%');
            });
        }

        // Timeframe filtering:
        // 'upcoming' => event_date >= today
        // 'last' / 'past' => event_date < today
        // 'all' => all events (ordered by event_date desc so latest are prominent)
        $today = now()->toDateString();
        if ($timeframe === 'upcoming') {
            $query->whereDate('event_date', '>=', $today)->orderBy('event_date', 'asc');
        } elseif ($timeframe === 'last' || $timeframe === 'past') {
            $query->whereDate('event_date', '<', $today)->orderBy('event_date', 'desc');
        } else {
            $query->orderBy('event_date', 'desc');
        }

        $events = $query->get();

        return view('user.events', compact('categories', 'events', 'timeframe', 'categorySlug'));
    }


    public function contact()
    {
        return view('user.contact');
    }
    

    public function show($eventId)
    {
        $event = Event::with('category.form.formFields')->findOrFail($eventId);
        $form = $event->category->form ?? null;
        // Map of belt_id => override fee for this event
        $eventBeltFeeMap = EventBeltFee::where('event_id', $event->id)->pluck('fee', 'belt_id');
        return view('user.form', compact('event', 'form', 'eventBeltFeeMap'));
    }

    public function registrationSuccess($id)
    {
        $registration = Registration::find($id);
        if (!$registration) {
            return redirect('/')->with('error', 'No registration found.');
        }
        return view('user.registration_success', compact('registration'));
    }


}