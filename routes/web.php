<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\BeltController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\FormSubmissionController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\User\ContactFormController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\RazorpayController;
use Illuminate\Support\Facades\Mail;


/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/')->group(function () {

    // Static Pages
    Route::get('/', [UserController::class, 'index'])->name('user.index');
    Route::get('/About', [UserController::class, 'about'])->name('user.about');
    Route::get('/Events', [UserController::class, 'events'])->name('user.events');
    Route::get('/Contact', [UserController::class, 'contact'])->name('user.contact');
    Route::view('/Refund-policy', 'user.refund-policy')->name('user.refund-policy');
    Route::view('/Terms&Conditions', 'user.terms-and-conditions')->name('user.terms-and-conditions');
    Route::view('/Privacy-Policy', 'user.privacy_policy')->name('user.privacy_policy');

    // Registration & Payment
    Route::get('/Register/{event}', [UserController::class, 'show'])->name('user.form.show');
    Route::post('/form/submit', [FormSubmissionController::class, 'submit'])->name('user.form.submit');
    Route::get('/form/success', [FormSubmissionController::class, 'success'])->name('user.form.success');
    Route::get('/registration-success/{id}', [UserController::class, 'registrationSuccess'])->name('user.registration_success');
    Route::get('/form/download', [FormSubmissionController::class, 'downloadPDF'])->name('user.form.download');

    // Razorpay
    Route::post('/razorpay/start', [RazorpayController::class, 'startRazorpay'])->name('user.razorpay.start');
    Route::post('/razorpay-success', [FormSubmissionController::class, 'Razorepaysuccess'])->name('user.razorpay.Razorepaysuccess');
    Route::post('/razorpay/webhook', [FormSubmissionController::class, 'razorpayWebhook'])->name('user.razorpay.webhook');

    // Contact Form
    Route::post('/contact', [ContactFormController::class, 'submit'])->name('contact.submit');
    Route::get('/belt-name/{id}', [BeltController::class, 'getBeltName']);

});


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');


/*
|--------------------------------------------------------------------------
| Admin Routes (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // Dashboard & Static Pages
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/messages', [MessageController::class, 'index'])->name('admin.message');
    Route::delete('/admin/messages/{id}', [MessageController::class, 'destroy'])->name('admin.message.delete');
    Route::view('/settings', 'admin.settings')->name('admin.settings');

    // Belt Routes
    Route::get('/belt', [BeltController::class, 'index'])->name('admin.belt');
    Route::post('/belt/store', [BeltController::class, 'store'])->name('admin.belt.store');
    Route::put('/belt/{id}', [BeltController::class, 'update'])->name('admin.belt.update');
    Route::delete('/belt/{id}', [BeltController::class, 'destroy'])->name('admin.belt.delete');

    // Category Routes
    Route::get('/category', [CategoryController::class, 'index'])->name('admin.category');
    Route::post('/category', [CategoryController::class, 'store'])->name('admin.category.store');
    Route::put('/category/{id}', [CategoryController::class, 'update'])->name('admin.category.update');
    Route::delete('/category/{id}', [CategoryController::class, 'destroy'])->name('admin.category.delete');
    Route::post('/category/{id}/form-save', [CategoryController::class, 'saveForm'])->name('admin.category.form.save');
    Route::get('/api/belts', [CategoryController::class, 'getBeltOptions']);

    // Event Routes
    Route::get('/events', [EventController::class, 'index'])->name('admin.event');
    Route::post('/events/store', [EventController::class, 'store'])->name('admin.event.store');
    Route::get('/events/belt-fees/defaults', [EventController::class, 'getDefaultBeltFees'])->name('admin.events.belt_fees.defaults');
    Route::get('/events/{id}/belt-fees', [EventController::class, 'getEventBeltFees'])->name('admin.events.belt_fees.event');
    Route::get('/events/{id}/form-builder', [EventController::class, 'formBuilder'])->name('admin.form_builder');
    Route::post('/events/{id}/form-fields', [EventController::class, 'saveFormFields'])->name('admin.event.form.save');
    Route::get('/events/{id}/form-fields', [EventController::class, 'getFormFields'])->name('admin.event.form.get');
    Route::get('/events/{id}/form-fields-modal', [EventController::class, 'getFormFieldsForModal'])->name('admin.event.form.modal');
    Route::post('/events/{id}/save-student', [EventController::class, 'saveStudentRegistration'])->name('admin.event.save.student');
    Route::post('/events/{id}/store-registration', [EventController::class, 'storeRegistration'])->name('admin.event.store.registration');
    Route::get('/events/{id}/user-form', [EventController::class, 'getUserForm'])->name('admin.event.user.form');
    Route::get('/test-form-fields/{id}', [EventController::class, 'testFormFields'])->name('admin.test.form.fields');
    Route::get('/belts', [EventController::class, 'getBelts'])->name('admin.belts');
    Route::get('/update-amounts', [EventController::class, 'updateAmountsFromPaymentIds'])->name('admin.update.amounts');
    Route::get('/payment-amounts', [EventController::class, 'paymentAmounts'])->name('admin.payment.amounts');
    Route::post('/payment-amounts/process', [EventController::class, 'processPaymentAmounts'])->name('admin.payment.amounts.process');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('admin.event.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('admin.event.delete');

    // Registration Management
    Route::get('/registrations', [EventController::class, 'showAllRegistrations'])->name('admin.registrations');
    Route::get('/pending', [EventController::class, 'showPendingRegistrations'])->name('admin.pending');
    Route::get('/events/{id}/registrations', [EventController::class, 'registrations'])->name('admin.events.registrations');
    Route::post('/registrations/update-status', [EventController::class, 'updateRegistrationStatus'])->name('admin.registrations.update-status');
    
    // Payment Testing
    Route::get('/test-payment-status', [RazorpayController::class, 'testPaymentStatus'])->name('admin.test.payment.status');
    Route::post('/capture-payment', [RazorpayController::class, 'capturePayment'])->name('admin.capture.payment');
    Route::post('/sync-payment-status', [RazorpayController::class, 'syncPaymentStatus'])->name('admin.sync.payment.status');
    
    // Admin Registration CRUD
    Route::get('/registrations/create', [EventController::class, 'createRegistration'])->name('admin.registrations.create');
    Route::post('/registrations/store', [EventController::class, 'storeRegistration'])->name('admin.registrations.store');
    Route::get('/registrations/{id}/edit', [EventController::class, 'editRegistration'])->name('admin.registrations.edit');
    Route::put('/registrations/{id}', [EventController::class, 'updateRegistration'])->name('admin.registrations.update');
    Route::delete('/registrations/{id}', [EventController::class, 'deleteRegistration'])->name('admin.registrations.delete');
    Route::post('/registrations/fetch-payment-amount', [EventController::class, 'fetchPaymentAmount'])->name('admin.registrations.fetch-payment-amount');
    
    Route::get('/event/export/{eventId}', [EventController::class, 'export'])->name('admin.event.export');
    Route::get('/event/{eventId}/export-pending', [EventController::class, 'exportPending'])->name('admin.event.export.pending');
    Route::get('/event/{eventId}/export-attended', [EventController::class, 'exportAttended'])->name('admin.event.export.attended');
    Route::get('/event/{eventId}/download-certificates', [EventController::class, 'downloadAttendedCertificates'])->name('admin.event.certificates.download');
    Route::post('/registrations/toggle-attendance', [EventController::class, 'toggleAttendance'])->name('admin.registrations.toggle-attendance');
    Route::post('/events/{eventId}/mark-all-attendance', [EventController::class, 'markAllAttendance'])->name('admin.event.mark-all-attendance');
    Route::get('/event/export-category-wise/{eventId}', [EventController::class, 'exportCategoryWise'])->name('admin.event.export.category');
    Route::get('/registration/{registrationId}/pdf', [EventController::class, 'viewPDF'])->name('admin.registration.viewpdf');
    Route::get('/event/{eventId}/registrations/pdf', [EventController::class, 'downloadAllPDFs'])->name('admin.event.pdf.all');
    Route::patch('/event/{id}/toggle-status', [EventController::class, 'toggleStatus'])->name('admin.event.toggleStatus');

    // Certificate (Legacy - for individual registration certificates)
    Route::get('/certificate/view/{registrationId}', [CertificateController::class, 'showRegistrationCertificate'])->name('admin.certificate.layout');
    Route::get('/registration/{id}/download-pdf', [EventController::class, 'downloadPDF'])->name('registration.download.pdf');
    
    // Certificate Event Management
    Route::get('/certificates', [CertificateController::class, 'index'])->name('admin.certificates.index');
    Route::get('/certificates/create', [CertificateController::class, 'create'])->name('admin.certificates.create');
    Route::post('/certificates/store', [CertificateController::class, 'store'])->name('admin.certificates.store');
    Route::get('/certificates/{id}/edit', [CertificateController::class, 'edit'])->name('admin.certificates.edit');
    Route::put('/certificates/{id}', [CertificateController::class, 'update'])->name('admin.certificates.update');
    
    // Certificate Template Management (must come before parameterized routes)
    Route::get('/certificates/templates', [CertificateController::class, 'templatesIndex'])->name('admin.certificates.templates.index');
    Route::get('/certificates/templates/create', [CertificateController::class, 'templatesCreate'])->name('admin.certificates.templates.create');
    Route::post('/certificates/templates/store', [CertificateController::class, 'templatesStore'])->name('admin.certificates.templates.store');
    Route::get('/certificates/templates/{id}/edit', [CertificateController::class, 'templatesEdit'])->name('admin.certificates.templates.edit');
    Route::put('/certificates/templates/{id}', [CertificateController::class, 'templatesUpdate'])->name('admin.certificates.templates.update');
    Route::delete('/certificates/templates/{id}', [CertificateController::class, 'templatesDestroy'])->name('admin.certificates.templates.destroy');
    
    // Certificate Event routes with parameters (must come after specific routes)
    Route::get('/certificates/{certificateId}/view/{registrationId}', [CertificateController::class, 'viewCertificate'])->name('admin.certificates.view');
    Route::get('/certificates/{id}/download-all', [CertificateController::class, 'downloadAll'])->name('admin.certificates.downloadAll');
    Route::get('/certificates/{id}', [CertificateController::class, 'show'])->name('admin.certificates.show');
});
Route::get('/test-mail', function () {
    Mail::raw('This is a test mail from Laravel.', function ($message) {
        $message->to('yourreceiver@gmail.com')
                ->subject('Laravel Test Mail');
    });
    return '✅ Test mail sent!';
});

Route::get('/debug-forms', function () {
    $categories = \App\Models\Admin\Category::with('form.formFields')->get();
    $events = \App\Models\Admin\Event::with('category.form.formFields')->get();
    
    return response()->json([
        'categories' => $categories->map(function($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'has_form' => $cat->form ? true : false,
                'form_fields_count' => $cat->form ? $cat->form->formFields->count() : 0
            ];
        }),
        'events' => $events->map(function($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'category_name' => $event->category->name ?? 'No category',
                'has_form' => $event->category->form ? true : false,
                'form_fields_count' => $event->category->form ? $event->category->form->formFields->count() : 0
            ];
        })
    ]);
});

Route::get('/setup-test-form', function () {
    // Create a test form for the first category
    $category = \App\Models\Admin\Category::first();
    if (!$category) {
        return 'No categories found. Please create a category first.';
    }
    
    // Create form if it doesn't exist
    $form = $category->form;
    if (!$form) {
        $form = \App\Models\Admin\Form::create([
            'category_id' => $category->id,
            'title' => 'Test Registration Form'
        ]);
    }
    
    // Create some basic form fields
    $fields = [
        ['label' => 'Student Name', 'type' => 'text', 'required' => 1],
        ['label' => 'Email', 'type' => 'email', 'required' => 1],
        ['label' => 'Phone Number', 'type' => 'tel', 'required' => 0],
        ['label' => 'Class', 'type' => 'select', 'required' => 1, 'options' => 'LKG,UKG,I STD,II STD,III STD,IV STD,V STD,VI STD,VII STD,VIII STD,IX STD,X STD,Above'],
        ['label' => 'School Name', 'type' => 'text', 'required' => 1],
        ['label' => 'Current Belt', 'type' => 'belt', 'required' => 1],
    ];
    
    // Clear existing fields
    $form->formFields()->delete();
    
    // Create new fields
    foreach ($fields as $index => $field) {
        \App\Models\Admin\FormField::create([
            'form_id' => $form->id,
            'label' => $field['label'],
            'type' => $field['type'],
            'required' => $field['required'],
            'order' => $index,
            'options' => isset($field['options']) ? json_encode(explode(',', $field['options'])) : null,
        ]);
    }
    
    return 'Test form created successfully for category: ' . $category->name . ' with ' . count($fields) . ' fields.';
});

Route::get('/check-db-structure', function () {
    $formColumns = \Illuminate\Support\Facades\Schema::getColumnListing('tbl_form');
    $categoryColumns = \Illuminate\Support\Facades\Schema::getColumnListing('tbl_category');
    $formFieldColumns = \Illuminate\Support\Facades\Schema::getColumnListing('tbl_form_field');
    
    return response()->json([
        'tbl_form_columns' => $formColumns,
        'tbl_category_columns' => $categoryColumns,
        'tbl_form_field_columns' => $formFieldColumns,
        'categories_count' => \App\Models\Admin\Category::count(),
        'forms_count' => \App\Models\Admin\Form::count(),
        'form_fields_count' => \App\Models\Admin\FormField::count(),
    ]);
});