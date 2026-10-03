<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Event;
use App\Models\Admin\Category;
use App\Models\Admin\Form;
use App\Models\Admin\FormField;
use App\Models\Admin\belt;
use App\Models\Admin\EventBeltFee;
use App\Exports\RegistrationExport;
use App\Exports\CategoryWiseExport;
use App\Exports\PendingRegistrationExport;
use App\Exports\AttendedCertificateExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Registration;
use App\Models\CertificateTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;



class EventController extends Controller
{
    /**
     * Display all events.
     */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'registrations']);

        // Search by title or venue
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            if ($categoryId !== 'all') {
                $query->where('category_id', $categoryId);
            }
        }

        // Filter by status (active / inactive)
        if ($request->has('status') && $request->input('status') !== '' && $request->input('status') !== 'all') {
            $query->where('is_active', (int)$request->input('status'));
        }

        // Timeframe filter (e.g., last_events, upcoming, past)
        $timeframe = $request->input('timeframe', 'all');
        $today = now()->toDateString();
        if ($timeframe === 'last' || $timeframe === 'past') {
            $query->where('event_date', '<', $today);
        } elseif ($timeframe === 'upcoming') {
            $query->where('event_date', '>=', $today);
        }

        // Sorting
        $sortOrder = $request->input('sort', 'desc');
        $query->orderBy('event_date', $sortOrder === 'asc' ? 'asc' : 'desc');

        $events = $query->get();
        $categories = Category::all();

        return view('admin.event', compact('events', 'categories'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'nullable|string',
            'category_id'     => 'required|exists:tbl_category,id',
            'event_date'      => 'required|date',
            'event_time'      => 'nullable',
            'venue'           => 'required|string',
            'description'     => 'nullable|string',
            'fee'             => 'nullable|numeric|min:0',
            'additional_fee'  => 'nullable|numeric|min:0',
            'customize_belt_fees' => 'nullable|boolean',
            'belt_fee'        => 'nullable|array',
            'belt_fee.*'      => 'nullable|numeric|min:0',
        ]);

        $event = Event::create([
            'title'          => $request->title??'Registration for KYU grading',
            'category_id'    => $request->category_id,
            'event_date'     => $request->event_date,
            'event_time'     => $request->event_time ?? 0,
            'venue'          => $request->venue,
            'description'    => $request->description,
            'fee'            => $request->fee,
            'additional_fee' => $request->additional_fee ?? 0,
        ]);

        if ($request->boolean('customize_belt_fees') && is_array($request->belt_fee)) {
            // Persist only overrides that differ from defaults and are provided
            $defaultFeesByBeltId = Belt::query()->pluck('fees', 'id');
            foreach ($request->belt_fee as $beltId => $fee) {
                if ($fee === null || $fee === '') { continue; }
                $feeValue = (float) $fee;
                $defaultFee = isset($defaultFeesByBeltId[$beltId]) ? (float) $defaultFeesByBeltId[$beltId] : null;
                if ($defaultFee === null || $feeValue !== $defaultFee) {
                    EventBeltFee::updateOrCreate(
                        ['event_id' => $event->id, 'belt_id' => (int) $beltId],
                        ['fee' => $feeValue]
                    );
                }
            }
        }

        return redirect()
            ->route('admin.event')
            ->with('success', 'Event created successfully!')
            ->with('qr_event_id', $event->id)
            ->with('qr_event_title', $event->title)
            ->with('qr_register_url', route('user.form.show', $event->id));
    }

    public function getDefaultBeltFees()
    {
        try {
            $belts = Belt::query()
                ->orderBy('id')
                ->get(['id', 'from_belt', 'to_belt', 'fees']);
            
            return response()->json([
                'data' => $belts,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching default belt fees: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'error' => 'Failed to load belt fees: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEventBeltFees($id)
    {
        $event = Event::findOrFail($id);
        $belts = Belt::query()
            ->orderBy('id')
            ->get(['id', 'from_belt', 'to_belt', 'fees']);
        
        // Fetch existing belt fee overrides from event_belt_fees table
        $eventBeltFeeMap = EventBeltFee::where('event_id', $event->id)
            ->pluck('fee', 'belt_id')
            ->toArray();
        
        $beltsWithOverrides = $belts->map(function ($belt) use ($eventBeltFeeMap) {
            $belt->override_fee = $eventBeltFeeMap[$belt->id] ?? null;
            return $belt;
        });
        
        return response()->json([
            'data' => $beltsWithOverrides,
            'has_overrides' => !empty($eventBeltFeeMap),
        ]);
    }

    /**
     * Update an existing event.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'           => 'required|string',
            'category_id'     => 'required|exists:tbl_category,id',
            'event_date'      => 'required|date',
            'event_time'      => 'required',
            'venue'           => 'required|string',
            'description'     => 'nullable|string',
            'fee'             => 'nullable|numeric|min:0',
            'additional_fee'  => 'nullable|numeric|min:0',
            'customize_belt_fees' => 'nullable|boolean',
            'belt_fee'        => 'nullable|array',
            'belt_fee.*'      => 'nullable|numeric|min:0',
        ]);

        $event = Event::findOrFail($id);

        $event->update([
            'title'          => $request->title,
            'category_id'    => $request->category_id,
            'event_date'     => $request->event_date,
            'event_time'     => $request->event_time,
            'venue'          => $request->venue,
            'description'    => $request->description,
            'fee'            => $request->fee,
            'additional_fee' => $request->additional_fee,
        ]);

        // Handle belt fee customization
        if ($request->boolean('customize_belt_fees') && is_array($request->belt_fee)) {
            // Persist only overrides that differ from defaults and are provided
            $defaultFeesByBeltId = Belt::query()->pluck('fees', 'id');
            foreach ($request->belt_fee as $beltId => $fee) {
                if ($fee === null || $fee === '') { 
                    // If fee is empty, remove override if it exists
                    EventBeltFee::where('event_id', $event->id)
                        ->where('belt_id', $beltId)
                        ->delete();
                    continue; 
                }
                $feeValue = (float) $fee;
                $defaultFee = isset($defaultFeesByBeltId[$beltId]) ? (float) $defaultFeesByBeltId[$beltId] : null;
                if ($defaultFee === null || $feeValue !== $defaultFee) {
                    EventBeltFee::updateOrCreate(
                        ['event_id' => $event->id, 'belt_id' => (int) $beltId],
                        ['fee' => $feeValue]
                    );
                } else {
                    // If override matches default, remove it
                    EventBeltFee::where('event_id', $event->id)
                        ->where('belt_id', $beltId)
                        ->delete();
                }
            }
        } else {
            // If customize_belt_fees is not checked, remove all overrides for this event
            EventBeltFee::where('event_id', $event->id)->delete();
        }

        return redirect()->route('admin.event')->with('success', 'Event updated successfully!');
    }

    /**
     * Delete an event and its associated form/fields.
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        if ($event->form) {
            $event->form->formFields()->delete();
            $event->form->delete();
        }

        $event->delete();

        return redirect()->route('admin.event')->with('success', 'Event deleted successfully.');
    }

    /**
     * Show form builder for an event. (optional/legacy)
     */
    public function formBuilder($id)
    {
        $event = Event::with('form.formFields')->findOrFail($id);
        return view('admin.form_builder', compact('event'));
    }

    /**
     * Save form fields for an event.
     */
    public function saveFormFields(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $form = $event->form ?? new Form();
        $form->event_id = $event->id;
        $form->title = $request->form_title;
        $form->save();

        $form->formFields()->delete();

        if ($request->has('fields')) {
            foreach ($request->fields as $index => $field) {
                $options = null;

                if ($field['type'] === 'belt') {
                    $options = Belt::pluck('from_belt')->toJson();
                } elseif (!empty($field['options'])) {
                    $options = json_encode(explode(',', $field['options']));
                }

                FormField::create([
                    'form_id'  => $form->id,
                    'label'    => $field['label'],
                    'type'     => $field['type'],
                    'required' => isset($field['required']) ? 1 : 0,
                    'order'    => $index,
                    'options'  => $options,
                ]);
            }
        }

        return redirect()->route('admin.event')->with('success', 'Form updated successfully!');
    }

    public function getFormFields($id)
    {
        try {
            $event = Event::with('category.form.formFields')->findOrFail($id);
            $category = $event->category;
            
            // If no form exists for this category, create a default one
            if (!$category->form) {
                $form = \App\Models\Admin\Form::create([
                    'category_id' => $category->id,
                    'title' => 'Default Registration Form'
                ]);
                
                // Create default form fields
                $defaultFields = [
                    ['label' => 'Student Name', 'type' => 'text', 'required' => 1, 'order' => 0],
                    ['label' => 'Email', 'type' => 'email', 'required' => 1, 'order' => 1],
                    ['label' => 'Phone Number', 'type' => 'tel', 'required' => 0, 'order' => 2],
                    ['label' => 'Class', 'type' => 'select', 'required' => 1, 'order' => 3, 'options' => json_encode(['LKG','UKG','I STD','II STD','III STD','IV STD','V STD','VI STD','VII STD','VIII STD','IX STD','X STD','Above'])],
                    ['label' => 'School Name', 'type' => 'text', 'required' => 1, 'order' => 4],
                    ['label' => 'Current Belt', 'type' => 'belt', 'required' => 1, 'order' => 5],
                ];
                
                // Add "Next Belt" field for Competition and KYU Grading / Belt Test categories
                // Categories: Competition, Camping, KYU Grading / Belt Test
                $categoryName = strtolower($category->name ?? '');
                if (str_contains($categoryName, 'competition') || 
                    str_contains($categoryName, 'kyu') || 
                    str_contains($categoryName, 'belt test')) {
                    $defaultFields[] = ['label' => 'Next Belt', 'type' => 'belt', 'required' => 1, 'order' => 6];
                }
                
                foreach ($defaultFields as $fieldData) {
                    \App\Models\Admin\FormField::create([
                        'form_id' => $form->id,
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'],
                        'required' => $fieldData['required'],
                        'order' => $fieldData['order'],
                        'options' => $fieldData['options'] ?? null,
                    ]);
                }
                
                // Reload the event with the new form
                $event = Event::with('category.form.formFields')->findOrFail($id);
            }
            
            $form = $event->category->form;
            
            if (!$form || !$form->formFields) {
                return response()->json(['success' => false, 'message' => 'No form fields found for this event category']);
            }

            $fields = $form->formFields->sortBy('order')->map(function ($field) {
                return [
                    'id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type,
                    'required' => $field->required,
                    'options' => $field->options,
                ];
            });

            return response()->json([
                'success' => true, 
                'fields' => $fields,
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'category_name' => $event->category->name ?? 'No category',
                    'fee' => $event->fee ?? 0,
                    'additional_fee' => $event->additional_fee ?? 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getFormFieldsForModal($id)
    {
        try {
            $event = Event::with('category')->findOrFail($id);
            $category = $event->category;
            
            if (!$category) {
                return response()->json(['success' => false, 'message' => 'No category found for this event']);
            }
            
            // Fetch form fields directly from tbl_form_field based on category
            $formFields = \App\Models\Admin\FormField::whereHas('form', function($query) use ($category) {
                $query->where('category_id', $category->id);
            })->orderBy('order', 'asc')->get();
            
            // If no form fields exist, create default ones
            if ($formFields->isEmpty()) {
                // First create a form for this category if it doesn't exist
                $form = \App\Models\Admin\Form::firstOrCreate(
                    ['category_id' => $category->id],
                    ['title' => 'Default Registration Form']
                );
                
                // Create default form fields
                $defaultFields = [
                    ['label' => 'Student Name', 'type' => 'text', 'required' => 1, 'order' => 0],
                    ['label' => 'Email', 'type' => 'email', 'required' => 1, 'order' => 1],
                    ['label' => 'Phone Number', 'type' => 'tel', 'required' => 0, 'order' => 2],
                    ['label' => 'Class', 'type' => 'select', 'required' => 1, 'order' => 3, 'options' => json_encode(['LKG','UKG','I STD','II STD','III STD','IV STD','V STD','VI STD','VII STD','VIII STD','IX STD','X STD','Above'])],
                    ['label' => 'School Name', 'type' => 'text', 'required' => 1, 'order' => 4],
                    ['label' => 'Current Belt', 'type' => 'belt', 'required' => 1, 'order' => 5],
                ];
                
                // Add "Next Belt" field for Competition and KYU Grading / Belt Test categories
                // Categories: Competition, Camping, KYU Grading / Belt Test
                $categoryName = strtolower($category->name ?? '');
                if (str_contains($categoryName, 'competition') || 
                    str_contains($categoryName, 'kyu') || 
                    str_contains($categoryName, 'belt test')) {
                    $defaultFields[] = ['label' => 'Next Belt', 'type' => 'belt', 'required' => 1, 'order' => 6];
                }
                
                foreach ($defaultFields as $fieldData) {
                    \App\Models\Admin\FormField::create([
                        'form_id' => $form->id,
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'],
                        'required' => $fieldData['required'],
                        'order' => $fieldData['order'],
                        'options' => $fieldData['options'] ?? null,
                    ]);
                }
                
                // Fetch the newly created fields
                $formFields = \App\Models\Admin\FormField::where('form_id', $form->id)
                    ->orderBy('order', 'asc')
                    ->get();
            }

            $fields = $formFields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type,
                    'required' => $field->required,
                    'options' => $field->options,
                    'order' => $field->order,
                ];
            });

            return response()->json([
                'success' => true, 
                'fields' => $fields,
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'category_name' => $category->name ?? 'No category',
                    'category_id' => $category->id,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveStudentRegistration(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            
            // Validate the request
            $request->validate([
                'student_data' => 'required|array',
                'status' => 'required|in:pending,paid',
                'amount' => 'nullable|numeric|min:0',
            ]);
            
            // Prepare the submitted data in the same format as storeRegistration
            $submittedData = [];
            foreach ($request->student_data as $fieldId => $value) {
                // Get the field details
                $field = \App\Models\Admin\FormField::find($fieldId);
                if ($field) {
                    $submittedData[] = [
                        'field_id' => $fieldId,
                        'label' => $field->label,
                        'type' => $field->type,
                        'value' => $value
                    ];
                }
            }
            
            // Generate registration code by finding the last registration code and incrementing
            $lastRegistration = Registration::whereNotNull('registration_code')
                ->where('registration_code', 'like', 'MENTORS%')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastRegistration) {
                // Extract the number from the last registration code (e.g., MENTORS0193 -> 193)
                $lastNumber = (int) substr($lastRegistration->registration_code, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                // If no registration exists, start from 1
                $nextNumber = 1;
            }
            
            $registrationCode = 'MENTORS' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
            // Create the registration using the same structure as storeRegistration
            $registration = Registration::create([
                'event_id' => $event->id,
                'submitted_data' => $submittedData,
                'amount' => $request->amount ?? 0,
                'status' => $request->status,
                'entered_by' => 'admin',
                'registration_code' => $registrationCode,
                'payment_id' => $request->payment_id ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Student registration created successfully!',
                'registration_id' => $registration->id,
                'registration_code' => $registrationCode
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating registration: ' . $e->getMessage()
            ]);
        }
    }

    public function getUserForm($id)
    {
        try {
            $event = Event::with('category.form.formFields')->findOrFail($id);
            $category = $event->category;
            
            // If no form exists for this category, create a default one
            if (!$category->form) {
                $form = \App\Models\Admin\Form::create([
                    'category_id' => $category->id,
                    'title' => 'Default Registration Form'
                ]);
                
                // Create default form fields
                $defaultFields = [
                    ['label' => 'Student Name', 'type' => 'text', 'required' => 1, 'order' => 0],
                    ['label' => 'Email', 'type' => 'email', 'required' => 1, 'order' => 1],
                    ['label' => 'Phone Number', 'type' => 'tel', 'required' => 0, 'order' => 2],
                    ['label' => 'Class', 'type' => 'select', 'required' => 1, 'order' => 3, 'options' => json_encode(['LKG','UKG','I STD','II STD','III STD','IV STD','V STD','VI STD','VII STD','VIII STD','IX STD','X STD','Above'])],
                    ['label' => 'School Name', 'type' => 'text', 'required' => 1, 'order' => 4],
                    ['label' => 'Current Belt', 'type' => 'belt', 'required' => 1, 'order' => 5],
                ];
                
                // Add "Next Belt" field for Competition and KYU Grading / Belt Test categories
                // Categories: Competition, Camping, KYU Grading / Belt Test
                $categoryName = strtolower($category->name ?? '');
                if (str_contains($categoryName, 'competition') || 
                    str_contains($categoryName, 'kyu') || 
                    str_contains($categoryName, 'belt test')) {
                    $defaultFields[] = ['label' => 'Next Belt', 'type' => 'belt', 'required' => 1, 'order' => 6];
                }
                
                foreach ($defaultFields as $fieldData) {
                    \App\Models\Admin\FormField::create([
                        'form_id' => $form->id,
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'],
                        'required' => $fieldData['required'],
                        'order' => $fieldData['order'],
                        'options' => $fieldData['options'] ?? null,
                    ]);
                }
                
                // Reload the event with the new form
                $event = Event::with('category.form.formFields')->findOrFail($id);
            }
            
            $form = $event->category->form;
            
            if (!$form || !$form->formFields) {
                return response('<div class="alert alert-warning">No form fields found for this event category</div>');
            }

            // Render the user form HTML
            return view('user.form', compact('event', 'form'))->render();
            
        } catch (\Exception $e) {
            return response('<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>');
        }
    }

    public function getBelts()
    {
        try {
            // Try to get belts from the belt model first
            if (class_exists('\App\Models\Admin\Belt')) {
                $belts = \App\Models\Admin\belt::select('id', 'from_belt', 'fees')
                    ->orderBy('id', 'asc')
                    ->get();
                
                // Check if we got any belts from database
                if ($belts->count() > 0) {
                    return response()->json($belts);
                }
            }
            
            // Try alternative approach - direct database query
            try {
                $belts = \DB::table('tbl_belt')
                    ->select('id', 'from_belt', 'fees')
                    ->orderBy('id', 'asc')
                    ->get();
                
                if ($belts->count() > 0) {
                    return response()->json($belts);
                }
            } catch (\Exception $e) {
                \Log::error('Direct DB query failed: ' . $e->getMessage());
            }
            
            // Fallback: return default belt options
            $defaultBelts = [
                ['id' => 1, 'from_belt' => 'White Belt', 'fees' => 500],
                ['id' => 2, 'from_belt' => 'Yellow Belt', 'fees' => 600],
                ['id' => 3, 'from_belt' => 'Orange Belt', 'fees' => 700],
                ['id' => 4, 'from_belt' => 'Green Belt', 'fees' => 800],
                ['id' => 5, 'from_belt' => 'Blue Belt', 'fees' => 900],
                ['id' => 6, 'from_belt' => 'Brown Belt', 'fees' => 1000],
                ['id' => 7, 'from_belt' => 'Black Belt', 'fees' => 1200],
            ];
            
            return response()->json($defaultBelts);
        } catch (\Exception $e) {
            \Log::error('Belt fetching error: ' . $e->getMessage());
            
            // Return default belts if there's any error
            $defaultBelts = [
                ['id' => 1, 'from_belt' => 'White Belt', 'fees' => 500],
                ['id' => 2, 'from_belt' => 'Yellow Belt', 'fees' => 600],
                ['id' => 3, 'from_belt' => 'Orange Belt', 'fees' => 700],
                ['id' => 4, 'from_belt' => 'Green Belt', 'fees' => 800],
                ['id' => 5, 'from_belt' => 'Blue Belt', 'fees' => 900],
                ['id' => 6, 'from_belt' => 'Brown Belt', 'fees' => 1000],
                ['id' => 7, 'from_belt' => 'Black Belt', 'fees' => 1200],
            ];
            
            return response()->json($defaultBelts);
        }
    }

    public function paymentAmounts()
    {
        // Get all registrations that have payment_id but amount is 0 or null
        $registrations = Registration::whereNotNull('payment_id')
            ->where(function($query) {
                $query->where('amount', 0)
                      ->orWhereNull('amount');
            })
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all registrations for display
        $allRegistrations = Registration::with('event')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.payment_amounts', compact('registrations', 'allRegistrations'));
    }

    public function processPaymentAmounts(Request $request)
    {
        try {
            $registrationIds = $request->input('registration_ids', []);
            $updated = 0;
            $errors = [];
            
            if (empty($registrationIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No registrations selected'
                ]);
            }
            
            foreach ($registrationIds as $registrationId) {
                try {
                    $registration = Registration::findOrFail($registrationId);
                    
                    if (!$registration->payment_id) {
                        $errors[] = "Registration ID {$registrationId}: No payment ID found";
                        continue;
                    }
                    
                    // Initialize Razorpay API
                    $api = new \Razorpay\Api\Api(config('razorpay.key_id'), config('razorpay.key_secret'));
                    
                    // Fetch payment details
                    $payment = $api->payment->fetch($registration->payment_id);
                    $amount = $payment->amount / 100; // Convert from paise to rupees
                    
                    // Update the registration
                    $registration->update(['amount' => $amount]);
                    $updated++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Registration ID {$registrationId}: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updated} registrations",
                'updated_count' => $updated,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payments: ' . $e->getMessage()
            ]);
        }
    }

    public function updateAmountsFromPaymentIds()
    {
        try {
            // Get all registrations that have payment_id but amount is 0 or null
            $registrations = Registration::whereNotNull('payment_id')
                ->where(function($query) {
                    $query->where('amount', 0)
                          ->orWhereNull('amount');
                })
                ->get();
            
            $updated = 0;
            $errors = [];
            
            foreach ($registrations as $registration) {
                try {
                    // Initialize Razorpay API
                    $api = new \Razorpay\Api\Api(config('razorpay.key_id'), config('razorpay.key_secret'));
                    
                    // Fetch payment details
                    $payment = $api->payment->fetch($registration->payment_id);
                    $amount = $payment->amount / 100; // Convert from paise to rupees
                    
                    // Update the registration
                    $registration->update(['amount' => $amount]);
                    $updated++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Registration ID {$registration->id}: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updated} registrations",
                'updated_count' => $updated,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating amounts: ' . $e->getMessage()
            ]);
        }
    }

    public function testFormFields($id)
    {
        try {
            $event = Event::with('category.form.formFields')->findOrFail($id);
            
            $debug = [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'category_id' => $event->category_id,
                'category_name' => $event->category->name ?? 'No category',
                'form_exists' => $event->category->form ? 'Yes' : 'No',
                'form_id' => $event->category->form->id ?? 'No form',
                'form_fields_count' => $event->category->form->formFields->count() ?? 0,
                'form_fields' => $event->category->form->formFields ?? []
            ];
            
            return response()->json($debug);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * View all registrations grouped by events.
     * By default, shows the current Academy Event with option to select previous events.
     */
    public function showAllRegistrations(Request $request)
    {
        $sort = $request->input('sort', 'desc'); // 'desc' or 'asc'
        $selectedEventId = $request->input('event_id');

        // Fetch all events for the filter dropdown
        $allEvents = Event::with('category')
            ->orderBy('event_date', $sort === 'asc' ? 'asc' : 'desc')
            ->get();

        // Identify current active Academy event (most recent active event or latest event)
        $currentEvent = Event::where('is_active', 1)
            ->orderBy('event_date', 'desc')
            ->first() ?? $allEvents->first();

        // If no filter is passed, default to current Academy Event
        if ($selectedEventId === null && $currentEvent) {
            $selectedEventId = (string)$currentEvent->id;
        }

        $query = Event::with(['category', 'registrations' => function ($query) {
            $query->where('status', 'paid')->orderBy('created_at', 'desc');
        }])
        ->withCount([
            'registrations as paid_count' => function ($query) {
                $query->where('status', 'paid');
            },
            'registrations as pending_count' => function ($query) {
                $query->where('status', '!=', 'paid');
            },
            'registrations as attended_count' => function ($query) {
                if (Schema::hasColumn('tbl_registration', 'is_attended')) {
                    $query->where('status', 'paid')->where(function($q) {
                        $q->where('is_attended', true)->orWhereNull('is_attended');
                    });
                } else {
                    $query->where('status', 'paid');
                }
            }
        ]);

        if ($selectedEventId && $selectedEventId !== 'all') {
            $query->where('id', $selectedEventId);
        }

        $events = $query->orderBy('event_date', $sort === 'asc' ? 'asc' : 'desc')->get();

        return view('admin.registrations', compact('events', 'allEvents', 'selectedEventId', 'currentEvent', 'sort'));
    }

    public function showPendingRegistrations(Request $request)
    {
        $eventId = $request->input('event_id');
        $allEvents = Event::with('category')->orderBy('event_date', 'desc')->get();
        $currentEvent = Event::where('is_active', 1)->orderBy('event_date', 'desc')->first() ?? $allEvents->first();

        // If no event_id passed and user hasn't explicitly asked for all, default to current event
        if ($eventId === null && $currentEvent) {
            $eventId = (string)$currentEvent->id;
        }
        
        $query = Event::with(['category', 'registrations' => function ($query) {
            $query->where('status', '!=', 'paid')->orderBy('created_at', 'desc');
        }]);
        
        // Filter by event_id if provided
        if ($eventId && $eventId !== 'all') {
            $query->where('id', $eventId);
        }
        
        $events = $query->orderBy('event_date', 'desc')->get();

        return view('admin.pending', compact('events', 'eventId', 'allEvents', 'currentEvent'));
    }

    /**
     * Toggle individual participant attendance.
     */
    public function toggleAttendance(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:tbl_registration,id',
        ]);

        try {
            $registration = Registration::findOrFail($request->registration_id);
            $newStatus = $request->has('is_attended') 
                ? filter_var($request->is_attended, FILTER_VALIDATE_BOOLEAN)
                : !($registration->is_attended ?? true);

            $registration->is_attended = $newStatus;
            if ($request->filled('rank')) {
                $registration->rank = $request->rank;
            }
            $registration->save();

            return response()->json([
                'success' => true,
                'is_attended' => (bool)$registration->is_attended,
                'message' => 'Attendance status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark all paid participants as attended or absent for an event.
     */
    public function markAllAttendance(Request $request, $eventId)
    {
        $attended = $request->input('status', 'attended') === 'attended';
        try {
            Registration::where('event_id', $eventId)
                ->where('status', 'paid')
                ->update(['is_attended' => $attended]);

            return response()->json([
                'success' => true,
                'is_attended' => $attended,
                'message' => 'All paid participants marked as ' . ($attended ? 'Attended' : 'Absent') . '.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateRegistrationStatus(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:tbl_registration,id',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            Registration::where('id', $request->registration_id)
                ->update([
                    'status' => 'paid',
                    'amount' => $request->amount
                ]);

            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createRegistration()
    {
        $events = Event::with('category')->get();
        return view('admin.create_registration', compact('events'));
    }

    public function storeRegistration(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'registration_data' => 'nullable|array',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,paid'
        ]);

        try {
            // Generate registration code by finding the last registration code and incrementing
            $lastRegistration = Registration::whereNotNull('registration_code')
                ->where('registration_code', 'like', 'MENTORS%')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastRegistration) {
                // Extract the number from the last registration code (e.g., MENTORS0193 -> 193)
                $lastNumber = (int) substr($lastRegistration->registration_code, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                // If no registration exists, start from 1
                $nextNumber = 1;
            }
            
            $registrationCode = 'MENTORS' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $registration = Registration::create([
                'event_id' => $request->event_id,
                'submitted_data' => $request->registration_data ?? [],
                'amount' => $request->amount ?? 0,
                'status' => $request->status,
                'entered_by' => 'admin',
                'registration_code' => $registrationCode,
                'payment_id' => $request->payment_id ?? null,
            ]);

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration created successfully!',
                    'registration_id' => $registration->id,
                    'registration_code' => $registrationCode
                ]);
            }

            return redirect()->route('admin.registrations')->with('success', 'Registration created successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating registration: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Error creating registration: ' . $e->getMessage());
        }
    }

    public function editRegistration($id)
    {
        $registration = Registration::with('event.category.form.formFields')->findOrFail($id);
        $events = Event::with('category')->get();
        return view('admin.edit_registration', compact('registration', 'events'));
    }

    public function updateRegistration(Request $request, $id)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'registration_data' => 'required|array',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,paid'
        ]);

        try {
            $registration = Registration::findOrFail($id);
            
            $registration->update([
                'event_id' => $request->event_id,
                'submitted_data' => $request->registration_data,
                'amount' => $request->amount ?? 0,
                'status' => $request->status,
                'entered_by' => 'admin'
            ]);

            return redirect()->route('admin.registrations')->with('success', 'Registration updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating registration: ' . $e->getMessage());
        }
    }

    public function deleteRegistration($id)
    {
        try {
            $registration = Registration::findOrFail($id);
            $registration->delete();
            
            return redirect()->route('admin.registrations')->with('success', 'Registration deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting registration: ' . $e->getMessage());
        }
    }

    public function fetchPaymentAmount(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string'
        ]);

        try {
            $api = new \Razorpay\Api\Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            $payment = $api->payment->fetch($request->payment_id);
            
            return response()->json([
                'success' => true,
                'amount' => $payment->amount / 100 // Convert from paise to rupees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found or invalid payment ID'
            ]);
        }
    }

    public function export($eventId)
    {
        $event = Event::with('registrations')->findOrFail($eventId);
        return Excel::download(new RegistrationExport($event), 'event_' . $eventId . '_registrations.xlsx');
    }

    /**
     * Export pending registrations with belt and fee details to Excel
     */
    public function exportPending($eventId)
    {
        $event = Event::with('registrations')->findOrFail($eventId);
        return Excel::download(new PendingRegistrationExport($event), 'event_' . $eventId . '_pending_registrations.xlsx');
    }

    /**
     * Export attended participants to Excel, formatted ready for Certificate Generation
     */
    public function exportAttended($eventId)
    {
        $event = Event::with('registrations')->findOrFail($eventId);
        return Excel::download(new AttendedCertificateExport($event), 'event_' . $eventId . '_attended_certificates.xlsx');
    }

    /**
     * Export category-wise registrations with ID, Name, School, Amount, Phone Number
     */
    public function exportCategoryWise(Request $request, $eventId)
    {
        $event = Event::with('registrations')->findOrFail($eventId);
        $categoryName = $event->category->name ?? 'All';
        
        // Get status from request (default to 'paid' for backward compatibility)
        $status = $request->input('status', 'paid');
        
        // Validate status
        if (!in_array($status, ['paid', 'pending'])) {
            $status = 'paid';
        }
        
        $statusLabel = $status === 'pending' ? 'pending' : 'paid';
        
        // Sanitize filename - remove invalid characters (/, \, and other special chars)
        $sanitizedCategoryName = preg_replace('/[\/\\\\:*?"<>|]/', '', $categoryName);
        $sanitizedCategoryName = str_replace(' ', '_', strtolower($sanitizedCategoryName));
        $fileName = 'category_wise_' . $sanitizedCategoryName . '_' . $statusLabel . '_' . $eventId . '.xlsx';
        
        return Excel::download(new CategoryWiseExport($event, $categoryName, $status), $fileName);
    }

    public function downloadAllPDFs($eventId)
    {
        $event = Event::with(['registrations' => function ($query) {
            $query->where('status', 'paid')->orderBy('id', 'asc');
        }])->findOrFail($eventId);

        $registrations = $event->registrations;

        // Sort registrations in the same order as Excel export
        $category = strtolower($event->category->name ?? '');
        
        // Helper function to extract field values
        $getVal = function ($reg, $keyword) {
            $field = collect($reg->submitted_data)->first(
                fn($item) => str_contains(strtolower($item['label']), strtolower($keyword))
            );
            return is_array($field['value'] ?? '')
                ? implode(', ', $field['value'])
                : ($field['value'] ?? '');
        };

        // Class groups for sorting
        $classGroups = [
            'LKG-UKG'        => ['LKG', 'UKG'],
            'I – II STD'     => ['I STD', 'II STD'],
            'III – IV STD'   => ['III STD', 'IV STD'],
            'V – VI STD'     => ['V STD', 'VI STD'],
            'VII – VIII STD' => ['VII STD', 'VIII STD'],
            'IX - X STD'     => ['IX STD', 'X STD'],
            'Above'          => ['Above'],
        ];

        $classGroupOrder = array_keys($classGroups);

        // Sort registrations by class order (LKG to higher)
        $sorted = $registrations->sort(function ($a, $b) use ($getVal, $category, $classGroups, $classGroupOrder) {
            $classA = $getVal($a, 'class');
            $classB = $getVal($b, 'class');

            // Map to grouped class
            $mappedClassA = 'N/A';
            $mappedClassB = 'N/A';
            
            foreach ($classGroups as $groupName => $values) {
                if (in_array($classA, $values, true)) {
                    $mappedClassA = $groupName;
                }
                if (in_array($classB, $values, true)) {
                    $mappedClassB = $groupName;
                }
            }

            // Primary sort: by class (LKG to higher)
            $posA = array_search($mappedClassA, $classGroupOrder);
            $posB = array_search($mappedClassB, $classGroupOrder);
            
            // If class not found in order, put it at the end
            if ($posA === false) $posA = 999;
            if ($posB === false) $posB = 999;
            
            if ($posA !== $posB) {
                return $posA <=> $posB;
            }

            // Secondary sort: by weight (for competitions)
            $weightA = (float)$getVal($a, 'weight');
            $weightB = (float)$getVal($b, 'weight');

            if ($weightA != $weightB) {
                return $weightA <=> $weightB;
            }

            // Tertiary sort: by ID
            return $a->id <=> $b->id;
        });

        $pdf = PDF::loadView('admin.registration_bulk_pdf', [
            'event' => $event,
            'registrations' => $sorted->values(),
        ]);

        return $pdf->download('event_'.$event->id.'_registrations.pdf');
    }


    public function viewPDF($registrationId)
    {
        $registration = Registration::findOrFail($registrationId);
        $event = $registration->event;

        return view('admin.registration_detail', [
            'registration' => $registration,
            'event' => $event,
            'formData' => $registration->submitted_data,
        ]);
    }

    public function downloadPDF($registrationId)
    {
        $registration = Registration::findOrFail($registrationId);
        $event = $registration->event;

        // Load Blade view into PDF
        $pdf = Pdf::loadView('admin.registration_detail', [
            'registration' => $registration,
            'event' => $event,
            'formData' => $registration->submitted_data,
        ]);

        // Download the PDF
        return $pdf->download('registration_'.$registration->id.'.pdf');
    }

    public function toggleStatus($id)
    {
        $event = Event::findOrFail($id);
        $event->is_active = !$event->is_active;
        $event->save();

        return redirect()->back()->with('success', 'Event status updated successfully.');
    }

    /**
     * View registrations for a specific event (both paid and pending).
     */
    public function registrations(Request $request, $id)
    {
        $event = Event::with('category')->findOrFail($id);
        $participantSearch = trim((string) $request->input('search', ''));

        $applyParticipantSearch = function ($query) use ($participantSearch) {
            if ($participantSearch === '') {
                return $query;
            }

            // Case-insensitive match (JSON/submitted_data LIKE can be case-sensitive)
            $term = '%' . mb_strtolower($participantSearch, 'UTF-8') . '%';

            return $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(registration_code) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(CAST(submitted_data AS CHAR)) LIKE ?', [$term]);
            });
        };
        
        // Fetch PAID registrations: status = 'paid', order by created_at DESC, paginate(20)
        $paidRegistrations = $applyParticipantSearch(
                Registration::where('event_id', $id)->where('status', 'paid')
            )
            ->orderBy('created_at', 'DESC')
            ->paginate(20)
            ->appends($request->only('search'));
        
        // Fetch PENDING registrations: status != 'paid', order by created_at DESC, no pagination
        $pendingRegistrations = $applyParticipantSearch(
                Registration::where('event_id', $id)->where('status', '!=', 'paid')
            )
            ->orderBy('created_at', 'DESC')
            ->get();
        
        // Calculate amount for each pending registration if missing, and sum total pending amount
        $totalPendingAmount = 0;
        foreach ($pendingRegistrations as $reg) {
            if (!empty($reg->amount) && (float)$reg->amount > 0) {
                $totalPendingAmount += (float)$reg->amount;
            } else {
                $submittedData = is_array($reg->submitted_data) 
                    ? $reg->submitted_data 
                    : json_decode($reg->submitted_data ?? '[]', true);
                $beltField = collect($submittedData)->first(
                    fn($item) => str_contains(strtolower($item['label'] ?? ''), 'belt')
                );
                $beltId = is_array($beltField['value'] ?? '')
                    ? ($beltField['value'][0] ?? null)
                    : ($beltField['value'] ?? null);

                $calcAmount = 0;
                if ($beltId) {
                    $eventBeltFee = DB::table('event_belt_fees')
                        ->where('event_id', $event->id)
                        ->where('belt_id', $beltId)
                        ->first();
                    if ($eventBeltFee && $eventBeltFee->fee > 0) {
                        $calcAmount = (float)$eventBeltFee->fee;
                    } else {
                        $belt = DB::table('tbl_belt')->where('id', $beltId)->first();
                        if ($belt && $belt->fees > 0) {
                            $calcAmount = (float)$belt->fees;
                        }
                    }
                }
                if ($calcAmount <= 0) {
                    $calcAmount = (float)($event->fee ?? 0) + (float)($event->additional_fee ?? 0);
                }
                $reg->amount = $calcAmount;
                $totalPendingAmount += $calcAmount;
            }
        }
        
        // Fetch all PAID registrations to accurately calculate total paid amount
        $allPaidRegistrations = Registration::where('event_id', $id)
            ->where('status', 'paid')
            ->get();

        $totalPaidAmount = 0;
        foreach ($allPaidRegistrations as $reg) {
            if (!empty($reg->amount) && (float)$reg->amount > 0) {
                $totalPaidAmount += (float)$reg->amount;
            } else {
                $submittedData = is_array($reg->submitted_data) 
                    ? $reg->submitted_data 
                    : json_decode($reg->submitted_data ?? '[]', true);
                $beltField = collect($submittedData)->first(
                    fn($item) => str_contains(strtolower($item['label'] ?? ''), 'belt')
                );
                $beltId = is_array($beltField['value'] ?? '')
                    ? ($beltField['value'][0] ?? null)
                    : ($beltField['value'] ?? null);

                $calcAmount = 0;
                if ($beltId) {
                    $eventBeltFee = DB::table('event_belt_fees')
                        ->where('event_id', $event->id)
                        ->where('belt_id', $beltId)
                        ->first();
                    if ($eventBeltFee && $eventBeltFee->fee > 0) {
                        $calcAmount = (float)$eventBeltFee->fee;
                    } else {
                        $belt = DB::table('tbl_belt')->where('id', $beltId)->first();
                        if ($belt && $belt->fees > 0) {
                            $calcAmount = (float)$belt->fees;
                        }
                    }
                }
                if ($calcAmount <= 0) {
                    $calcAmount = (float)($event->fee ?? 0) + (float)($event->additional_fee ?? 0);
                }
                $totalPaidAmount += $calcAmount;
            }
        }

        $totalFullAmount = $totalPaidAmount + $totalPendingAmount;
        
        // Count attended participants
        $hasAttendedCol = Schema::hasColumn('tbl_registration', 'is_attended');
        $attendedCount = Registration::where('event_id', $id)
            ->where('status', 'paid')
            ->when($hasAttendedCol, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('is_attended', true)->orWhereNull('is_attended');
                });
            })
            ->count();

        // Belt-wise Participant Breakdown
        $allBelts = DB::table('tbl_belt')->orderBy('id', 'asc')->get();
        $beltCounts = [];
        foreach ($allBelts as $b) {
            $beltCounts[$b->id] = [
                'name' => $b->from_belt . ($b->to_belt ? ' → ' . $b->to_belt : ''),
                'from_belt' => $b->from_belt,
                'to_belt' => $b->to_belt,
                'paid' => 0,
                'pending' => 0,
                'total' => 0,
            ];
        }
        $otherBeltCount = ['name' => 'Other / Unspecified', 'from_belt' => 'Other', 'to_belt' => '', 'paid' => 0, 'pending' => 0, 'total' => 0];

        $extractBeltId = function ($reg) {
            $data = is_array($reg->submitted_data) ? $reg->submitted_data : json_decode($reg->submitted_data ?? '[]', true);
            $field = collect($data)->first(fn($item) => str_contains(strtolower($item['label'] ?? ''), 'belt'));
            $val = is_array($field['value'] ?? '') ? ($field['value'][0] ?? null) : ($field['value'] ?? null);
            return (!empty($val) && is_numeric($val)) ? (int)$val : null;
        };

        foreach ($allPaidRegistrations as $reg) {
            $bId = $extractBeltId($reg);
            if ($bId && isset($beltCounts[$bId])) {
                $beltCounts[$bId]['paid']++;
                $beltCounts[$bId]['total']++;
            } else {
                $otherBeltCount['paid']++;
                $otherBeltCount['total']++;
            }
        }

        foreach ($pendingRegistrations as $reg) {
            $bId = $extractBeltId($reg);
            if ($bId && isset($beltCounts[$bId])) {
                $beltCounts[$bId]['pending']++;
                $beltCounts[$bId]['total']++;
            } else {
                $otherBeltCount['pending']++;
                $otherBeltCount['total']++;
            }
        }

        if ($otherBeltCount['total'] > 0) {
            $beltCounts['other'] = $otherBeltCount;
        }

        $allEvents = Event::with('category')->orderBy('event_date', 'desc')->get();

        return view('admin.event_registrations', compact(
            'event',
            'paidRegistrations',
            'pendingRegistrations',
            'totalPaidAmount',
            'totalPendingAmount',
            'totalFullAmount',
            'attendedCount',
            'allEvents',
            'beltCounts'
        ));
    }

    /**
     * Download certificate PDFs for all attended participants in this event.
     * If 1 attended participant: downloads their certificate PDF directly.
     * If multiple attended participants: bundles all into a ZIP archive.
     */
    public function downloadAttendedCertificates($eventId)
    {
        $event = Event::with('category')->findOrFail($eventId);

        $hasAttendedCol = Schema::hasColumn('tbl_registration', 'is_attended');
        $attendedRegistrations = Registration::where('event_id', $eventId)
            ->where('status', 'paid')
            ->when($hasAttendedCol, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('is_attended', true)->orWhereNull('is_attended');
                });
            })
            ->orderBy('id', 'asc')
            ->get();

        if ($attendedRegistrations->isEmpty()) {
            return redirect()->back()->with('error', 'No attended paid participants found for this event to generate certificates.');
        }

        $participants = [];
        foreach ($attendedRegistrations as $reg) {
            $submittedData = is_array($reg->submitted_data) 
                ? $reg->submitted_data 
                : json_decode($reg->submitted_data ?? '[]', true);

            $getVal = function ($keyword) use ($submittedData) {
                $field = collect($submittedData)->first(
                    fn($item) => str_contains(strtolower($item['label'] ?? ''), strtolower($keyword))
                );
                return is_array($field['value'] ?? '')
                    ? implode(', ', $field['value'])
                    : ($field['value'] ?? '');
            };

            // Belt identification
            $beltId = $getVal('belt');
            $belt = null;
            if (!empty($beltId)) {
                try {
                    $belt = DB::table('tbl_belt')->where('id', $beltId)->first();
                } catch (\Throwable $e) {
                    $belt = null;
                }
            }
            $currentBelt = $belt?->from_belt ?? 'N/A';

            // Next belt identification
            $nextBeltField = collect($submittedData)->first(function ($item) {
                if (!isset($item['label'])) return false;
                $label = strtolower(trim($item['label']));
                $type = strtolower(trim($item['type'] ?? ''));
                if ($label === 'next belt') return true;
                if ($type === 'belt') return str_contains($label, 'next') && !str_contains($label, 'current');
                return false;
            });

            $nextBeltId = '';
            if ($nextBeltField && isset($nextBeltField['value'])) {
                $nextBeltId = is_array($nextBeltField['value'])
                    ? implode(', ', $nextBeltField['value'])
                    : (string)($nextBeltField['value'] ?? '');
                $nextBeltId = trim($nextBeltId);
            }

            if (!empty($nextBeltId) && is_numeric($nextBeltId)) {
                try {
                    $nextBelt = DB::table('tbl_belt')->where('id', $nextBeltId)->value('from_belt') ?? 'N/A';
                } catch (\Throwable $e) {
                    $nextBelt = 'N/A';
                }
            } else {
                $nextBelt = $belt?->to_belt ?? 'N/A';
            }

            $name = $getVal('name') ?: $getVal('student name') ?: 'Participant';
            $school = $getVal('school') ?: $getVal('school name');
            $place = trim((string) ($event->venue ?? ''));
            $rank = !empty($reg->rank) ? $reg->rank : 'Pass';
            $categoryName = $event->category->name ?? 'Belt Exam';
            $dateOfReg = !empty($event->event_date) 
                ? \Carbon\Carbon::parse($event->event_date)->format('d M Y') 
                : (!empty($reg->created_at) ? $reg->created_at->format('d M Y') : 'N/A');

            $participants[] = [
                'registration'    => $reg,
                'registration_id' => $reg->registration_code ?: ('REG-' . $reg->id),
                'name'            => $name,
                'place'           => $place,
                'school'          => $school ?: 'N/A',
                'current_belt'    => $currentBelt,
                'next_belt'       => $nextBelt,
                'rank'            => $rank,
                'category'        => $categoryName,
                'date_of_reg'     => $dateOfReg,
            ];
        }

        $beltKeys = ['yellow', 'orange', 'green', 'blue', 'purple', 'brown'];
        $templates = CertificateTemplate::all();
        $beltTemplateCache = [];
        foreach ($templates as $t) {
            if ($t->certificate_type === 'belt') {
                $norm = strtolower(trim(preg_replace('/\s*belt\s*$/i', '', $t->name)));
                foreach ($beltKeys as $k) {
                    if ($k === $norm || str_contains($norm, $k)) {
                        $beltTemplateCache[$k] = $t;
                        break;
                    }
                }
            }
        }

        $isCompetition = str_contains(strtolower($event->category->name ?? ''), 'competition');
        // Exact 72 DPI PDF points: 1 cm = 28.3464567 pt
        // Belt Certificate (24cm x 33cm): 680.315 pt x 935.433 pt
        // Competition Certificate (21cm x 29.7cm / A4): 595.276 pt x 841.89 pt
        $paperSize = $isCompetition ? [0, 0, 595.276, 841.89] : [0, 0, 680.315, 935.433];
        $certType = $isCompetition ? 'competition' : 'belt';

        $generatePdfForParticipant = function ($participant) use ($beltTemplateCache, $templates, $isCompetition, $certType, $paperSize, $event, $beltKeys) {
            $template = null;
            if ($isCompetition) {
                $template = $templates->firstWhere('certificate_type', 'competition') ?? $templates->first();
            } else {
                $nextBelt = $participant['next_belt'] ?? '';
                $norm = strtolower(trim(preg_replace('/\s*belt\s*$/i', '', $nextBelt)));
                foreach ($beltKeys as $k) {
                    if ($k === $norm || str_contains($norm, $k)) {
                        $template = $beltTemplateCache[$k] ?? null;
                        break;
                    }
                }
                if (!$template) {
                    $template = $templates->firstWhere('certificate_type', 'belt') ?? $templates->first();
                }
            }

            $backgroundImage = null;
            if ($template && $template->background_image) {
                $imagePath = storage_path('app/public/' . $template->background_image);
                if (!file_exists($imagePath)) {
                    $imagePath = public_path('storage/' . $template->background_image);
                }
                if (file_exists($imagePath)) {
                    $backgroundImage = 'data:' . mime_content_type($imagePath) . ';base64,' . base64_encode(file_get_contents($imagePath));
                }
            }

            $certObj = (object)[
                'id' => $event->id,
                'event_title' => $event->title,
                'event_date' => $event->event_date,
                'venue' => trim((string) ($event->venue ?? '')),
                'certificate_type' => $certType,
            ];

            return Pdf::loadView('admin.certificates.certificate_pdf', [
                'certificate' => $certObj,
                'participant' => $participant,
                'backgroundImage' => $backgroundImage,
                'certType' => $certType,
            ])->setPaper($paperSize, 'portrait')
              ->setOption('enable-html5-parser', true)
              ->setOption('enable-local-file-access', true);
        };

        if (count($participants) === 1) {
            $p = $participants[0];
            $pdf = $generatePdfForParticipant($p);
            $safeName = Str::slug($p['name']) ?: 'certificate';
            return $pdf->download($safeName . '_certificate.pdf');
        }

        if (request()->query('format') === 'zip') {
            $zipFileName = 'event_' . $event->id . '_attended_certificates_' . time() . '.zip';
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $zipPath = $tempDir . '/' . $zipFileName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                return redirect()->back()->with('error', 'Could not create certificates ZIP archive.');
            }

            foreach ($participants as $p) {
                $pdf = $generatePdfForParticipant($p);
                $safeName = Str::slug($p['name']) ?: 'certificate';
                $zip->addFromString($safeName . '_' . $p['registration_id'] . '.pdf', $pdf->output());
            }

            $zip->close();
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        // Single combined PDF with each certificate on a separate page
        $items = [];
        foreach ($participants as $p) {
            $template = null;
            if ($isCompetition) {
                $template = $templates->firstWhere('certificate_type', 'competition') ?? $templates->first();
            } else {
                $nextBelt = $p['next_belt'] ?? '';
                $norm = strtolower(trim(preg_replace('/\s*belt\s*$/i', '', $nextBelt)));
                foreach ($beltKeys as $k) {
                    if ($k === $norm || str_contains($norm, $k)) {
                        $template = $beltTemplateCache[$k] ?? null;
                        break;
                    }
                }
                if (!$template) {
                    $template = $templates->firstWhere('certificate_type', 'belt') ?? $templates->first();
                }
            }

            $backgroundImage = null;
            if ($template && $template->background_image) {
                $imagePath = storage_path('app/public/' . $template->background_image);
                if (!file_exists($imagePath)) {
                    $imagePath = public_path('storage/' . $template->background_image);
                }
                if (file_exists($imagePath)) {
                    $backgroundImage = 'data:' . mime_content_type($imagePath) . ';base64,' . base64_encode(file_get_contents($imagePath));
                }
            }

            $certObj = (object)[
                'id' => $event->id,
                'event_title' => $event->title,
                'event_date' => $event->event_date,
                'venue' => trim((string) ($event->venue ?? '')),
                'certificate_type' => $certType,
            ];

            $items[] = [
                'certificate' => $certObj,
                'participant' => $p,
                'backgroundImage' => $backgroundImage,
            ];
        }

        $pdf = Pdf::loadView('admin.certificates.certificates_bulk_pdf', [
            'items' => $items,
            'certType' => $certType,
        ])->setPaper($paperSize, 'portrait')
          ->setOption('enable-html5-parser', true)
          ->setOption('enable-local-file-access', true);

        $safeTitle = Str::slug($event->title ?: 'attended') . '_attended_certificates.pdf';
        return $pdf->download($safeTitle);
    }
}