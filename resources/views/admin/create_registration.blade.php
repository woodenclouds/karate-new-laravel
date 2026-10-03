@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<style>
    .form-section {
        background-color: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(255, 0, 0, 0.2);
        max-width: 1000px;
        margin: auto;
        color: #000;
    }

    .section-title {
        font-size: 2rem;
        font-weight: 700;
        color: #b30000;
        text-align: center;
        margin-bottom: 15px;
    }

    .event-details {
        text-align: center;
        margin-bottom: 30px;
    }

    .event-details p {
        margin: 0;
        font-size: 1rem;
        color: #444;
    }

    .form-label {
        font-weight: 600;
        color: #333;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        padding: 10px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #b30000;
        box-shadow: 0 0 0 0.2rem rgba(179, 0, 0, 0.25);
    }

    .btn-next {
        background-color: #b30000;
        color: #fff;
        border-radius: 25px;
        padding: 10px 30px;
        border: none;
        font-weight: 600;
    }

    .btn-next:hover {
        background-color: #8c0000;
    }

    .submit-btn-wrapper {
        text-align: center;
        margin-top: 30px;
    }

    @media (max-width: 767px) {
        .col-md-6 {
            margin-bottom: 20px;
        }
    }
</style>

<main class="page-content">
    <div class="container py-4">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Create New Registration</h2>
            <a href="{{ route('admin.registrations') }}" class="btn btn-secondary btn-sm">← Back to Registrations</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Simplified Registration Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> Create New Registration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="event_id" class="form-label">Event <span class="text-danger">*</span></label>
                                    <select class="form-select" id="event_id" name="event_id" required>
                                        <option value="">Select Event</option>
                                        @foreach($events as $event)
                                            <option value="{{ $event->id }}">{{ $event->title }} - {{ $event->event_date }}-{{ $event->venue }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount (₹)</label>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="button" class="btn btn-success w-100" onclick="openStudentModal()">
                                            <i class="fas fa-user-plus"></i> Add Student
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_id" class="form-label">Payment ID (Optional)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="payment_id" placeholder="Enter Razorpay Payment ID">
                                        <button type="button" class="btn btn-outline-primary" onclick="fetchPaymentAmount()">Fetch Amount</button>
                                    </div>
                                    <small class="form-text text-muted">Enter Razorpay payment ID to automatically fetch the amount</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <a href="{{ route('admin.registrations') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Registrations
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- Student Details Container -->
        <div id="dynamicFormContainer"></div>
    </div>
</main>

<!-- Student Registration Modal -->
<div class="modal fade" id="studentRegistrationModal" tabindex="-1" aria-labelledby="studentRegistrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentRegistrationModalLabel">Student Registration Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="studentForm">
                    <div id="studentFormFields">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading form fields...</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStudentData()">Save Student</button>
            </div>
        </div>
    </div>
</div>

<script>
let formFieldsData = {};

document.getElementById('event_id').addEventListener('change', function() {
    const eventId = this.value;
    if (eventId) {
        loadEventForm(eventId);
    } else {
        resetForm();
    }
});

function loadEventForm(eventId) {
    console.log('Loading form fields for event:', eventId);
    
    // Show loading indicator
    document.getElementById('formFields').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading form fields...</p>
        </div>
    `;
    
    fetch(`/admin/events/${eventId}/form-fields`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Form fields data:', data);
            if (data.success && data.fields) {
                renderFormFields(data.fields);
            } else {
                console.log('No form fields found, showing fallback form');
                showFallbackForm();
            }
        })
        .catch(error => {
            console.error('Error loading form fields:', error);
            console.log('Error occurred, showing fallback form');
            showFallbackForm();
        });
}

function renderFormFields(fields) {
    console.log('Rendering form fields:', fields);
    let html = '';
    fields.forEach((field, index) => {
        html += `<div class="mb-3">`;
        html += `<label for="field_${index}" class="form-label">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>`;
        
        // Add hidden fields for label and type
        html += `<input type="hidden" name="registration_data[${index}][label]" value="${field.label}">`;
        html += `<input type="hidden" name="registration_data[${index}][type]" value="${field.type}">`;
        
        switch(field.type) {
            case 'text':
            case 'email':
            case 'tel':
                html += `<input type="${field.type}" class="form-control" id="field_${index}" name="registration_data[${index}][value]" placeholder="Enter ${field.label}" ${field.required ? 'required' : ''}>`;
                break;
            case 'select':
                html += `<select class="form-select" id="field_${index}" name="registration_data[${index}][value]" ${field.required ? 'required' : ''}>`;
                html += `<option value="">Select ${field.label}</option>`;
                if (field.options) {
                    const options = JSON.parse(field.options);
                    options.forEach(option => {
                        html += `<option value="${option}">${option}</option>`;
                    });
                }
                html += `</select>`;
                break;
            case 'belt':
                html += `<select class="form-select" id="field_${index}" name="registration_data[${index}][value]" ${field.required ? 'required' : ''}>`;
                html += `<option value="">Select Belt</option>`;
                html += `</select>`;
                break;
        }
        
        html += `</div>`;
    });
    
    console.log('Generated HTML:', html);
    document.getElementById('formFields').innerHTML = html;
    
    // Load belts for all belt fields after rendering
    fields.forEach((field, index) => {
        if (field.type === 'belt') {
            loadBelts(index);
        }
    });
}

function loadBelts(fieldIndex) {
    fetch('/api/belts')
        .then(response => response.json())
        .then(belts => {
            const select = document.querySelector(`select[name="registration_data[${fieldIndex}][value]"]`);
            belts.forEach(belt => {
                const option = document.createElement('option');
                option.value = belt.id;
                option.textContent = belt.from_belt;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading belts:', error));
}

function showFallbackForm() {
    console.log('Showing fallback form');
    document.getElementById('formFields').style.display = 'none';
    document.getElementById('fallbackFormFields').style.display = 'block';
    
    // Load belts for the fallback form
    loadBelts(5); // Index 5 is the current_belt field
}

function fetchPaymentAmount() {
    const paymentId = document.getElementById('payment_id').value;
    if (!paymentId) {
        alert('Please enter a payment ID');
        return;
    }

    fetch('{{ route("admin.registrations.fetch-payment-amount") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            payment_id: paymentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('amount').value = data.amount;
            alert('Amount fetched successfully: ₹' + data.amount);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error fetching payment amount');
    });
}

function testFormFields() {
    const eventId = document.getElementById('event_id').value;
    if (!eventId) {
        alert('Please select an event first');
        return;
    }
    
    // Test the modal endpoint
    fetch(`/admin/events/${eventId}/form-fields-modal`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('Test response status:', response.status);
            console.log('Test response headers:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('Test response text:', text);
            try {
                const data = JSON.parse(text);
                console.log('Test parsed data:', data);
                alert('API working! Check console for details.');
            } catch (e) {
                console.log('Response is not JSON:', text);
                alert('API response is not JSON. Check console.');
            }
        })
        .catch(error => {
            console.error('Test error:', error);
            alert('API Error: ' + error.message);
        });
}

function openStudentModal() {
    const eventId = document.getElementById('event_id').value;
    if (!eventId) {
        alert('Please select an event first');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('studentRegistrationModal'));
    modal.show();
    
    // Load the form fields
    loadStudentFormFields(eventId);
}

function loadStudentFormFields(eventId) {
    fetch(`/admin/events/${eventId}/form-fields-modal`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Form fields data:', data);
            
            if (data.success && data.fields) {
                renderStudentFormFields(data.fields);
            } else {
                document.getElementById('studentFormFields').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No form fields found for this event.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading form fields:', error);
            document.getElementById('studentFormFields').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error loading form fields: ${error.message}
                </div>
            `;
        });
}

function renderStudentFormFields(fields) {
    let html = '';
    
    fields.forEach((field, index) => {
        const fieldName = `student_data[${field.id}]`;
        const label = field.label;
        const required = field.required ? 'required' : '';
        const options = field.options ? JSON.parse(field.options) : [];
        const type = field.type;
        
        html += `<div class="mb-3">`;
        html += `<label class="form-label">${label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>`;
        
        switch(type) {
            case 'text':
            case 'email':
            case 'tel':
            case 'number':
                html += `<input type="${type}" name="${fieldName}" class="form-control" ${required}>`;
                break;
                
            case 'date':
                html += `<input type="date" name="${fieldName}" class="form-control" ${required}>`;
                break;
                
            case 'textarea':
                html += `<textarea name="${fieldName}" class="form-control" rows="3" ${required}></textarea>`;
                break;
                
            case 'select':
                html += `<select name="${fieldName}" class="form-select" ${required}>`;
                html += `<option value="">-- Select --</option>`;
                options.forEach(option => {
                    html += `<option value="${option}">${option}</option>`;
                });
                html += `</select>`;
                break;
                
            case 'belt':
                html += `<select name="${fieldName}" id="modal_belt_${field.id}" class="form-select" ${required}>`;
                html += `<option value="">-- Select Belt --</option>`;
                html += `</select>`;
                break;
                
            case 'radio':
                html += `<div class="d-flex flex-wrap gap-3 pt-2">`;
                options.forEach(option => {
                    html += `<div class="form-check">`;
                    html += `<input type="radio" name="${fieldName}" value="${option}" class="form-check-input" ${required}>`;
                    html += `<label class="form-check-label">${option}</label>`;
                    html += `</div>`;
                });
                html += `</div>`;
                break;
                
            case 'checkbox':
                html += `<div class="d-flex flex-wrap gap-3 pt-2">`;
                options.forEach((option, optIndex) => {
                    html += `<div class="form-check me-3">`;
                    html += `<input type="checkbox" name="${fieldName}[]" value="${option}" class="form-check-input" id="${fieldName}_${optIndex}" ${required}>`;
                    html += `<label class="form-check-label" for="${fieldName}_${optIndex}">${option}</label>`;
                    html += `</div>`;
                });
                html += `</div>`;
                break;
                
            case 'file':
                html += `<input type="file" name="${fieldName}" class="form-control" ${required}>`;
                break;
        }
        
        html += `</div>`;
    });
    
    document.getElementById('studentFormFields').innerHTML = html;
    
    // Load belts for belt fields
    fields.forEach(field => {
        if (field.type === 'belt') {
            loadModalBelts(field.id);
        }
    });
}

function loadModalBelts(fieldId) {
    fetch('/admin/belts', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Belt API response:', data);
            const select = document.getElementById(`modal_belt_${fieldId}`);
            if (!select) return;
            
            // Clear existing options except the first one
            select.innerHTML = '<option value="">-- Select Belt --</option>';
            
            // Handle both array and object responses
            const belts = Array.isArray(data) ? data : [];
            console.log('Processed belts:', belts);
            
            belts.forEach(belt => {
                const option = document.createElement('option');
                option.value = belt.from_belt || belt.belt_name || belt.name || belt;
                option.textContent = `${belt.from_belt || belt.belt_name || belt.name || belt} - ₹${belt.fees || belt.amount || 500}`;
                option.setAttribute('data-fees', belt.fees || belt.amount || 500);
                select.appendChild(option);
            });
            
            // If no belts loaded, add default options
            if (belts.length === 0) {
                console.log('No belts found, using defaults');
                const defaultBelts = [
                    { id: 1, from_belt: 'White Belt', fees: 500 },
                    { id: 2, from_belt: 'Yellow Belt', fees: 600 },
                    { id: 3, from_belt: 'Orange Belt', fees: 700 },
                    { id: 4, from_belt: 'Green Belt', fees: 800 },
                    { id: 5, from_belt: 'Blue Belt', fees: 900 },
                    { id: 6, from_belt: 'Brown Belt', fees: 1000 },
                    { id: 7, from_belt: 'Black Belt', fees: 1200 }
                ];
                
                defaultBelts.forEach(belt => {
                    const option = document.createElement('option');
                    option.value = belt.from_belt;
                    option.textContent = `${belt.from_belt} - ₹${belt.fees}`;
                    option.setAttribute('data-fees', belt.fees);
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading belts:', error);
            
            // Fallback: add default belt options
            const select = document.getElementById(`modal_belt_${fieldId}`);
            if (select) {
                select.innerHTML = '<option value="">-- Select Belt --</option>';
                
                const defaultBelts = [
                    { id: 1, from_belt: 'White Belt', fees: 500 },
                    { id: 2, from_belt: 'Yellow Belt', fees: 600 },
                    { id: 3, from_belt: 'Orange Belt', fees: 700 },
                    { id: 4, from_belt: 'Green Belt', fees: 800 },
                    { id: 5, from_belt: 'Blue Belt', fees: 900 },
                    { id: 6, from_belt: 'Brown Belt', fees: 1000 },
                    { id: 7, from_belt: 'Black Belt', fees: 1200 }
                ];
                
                defaultBelts.forEach(belt => {
                    const option = document.createElement('option');
                    option.value = belt.from_belt;
                    option.textContent = `${belt.from_belt} - ₹${belt.fees}`;
                    option.setAttribute('data-fees', belt.fees);
                    select.appendChild(option);
                });
            }
        });
}

function saveStudentData() {
    const form = document.getElementById('studentForm');
    const formData = new FormData(form);
    
    // Convert FormData to object with proper field mapping
    const studentData = {};
    for (let [key, value] of formData.entries()) {
        // Extract field ID from key like "student_data[123]"
        if (key.startsWith('student_data[') && key.endsWith(']')) {
            const fieldId = key.match(/student_data\[(\d+)\]/)[1];
            studentData[fieldId] = value;
        }
    }
    
    // Get event ID
    const eventId = document.getElementById('event_id').value;
    if (!eventId) {
        alert('Please select an event first');
        return;
    }
    
    // Get status and amount from main form
    const status = document.getElementById('status').value;
    const amount = document.getElementById('amount').value || 0;
    const paymentId = document.getElementById('payment_id').value || null;
    
    // Validate required fields
    const requiredFields = document.querySelectorAll('#studentFormFields input[required], #studentFormFields select[required], #studentFormFields textarea[required]');
    let isValid = true;
    let missingFields = [];
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            const label = field.previousElementSibling ? field.previousElementSibling.textContent.replace('*', '').trim() : 'Field';
            missingFields.push(label);
        }
    });
    
    if (!isValid) {
        alert('Please fill in all required fields: ' + missingFields.join(', '));
        return;
    }
    
    // Generate a temporary registration code for display
    const tempRegistrationCode = 'TEMP' + Date.now();
    
    // Store student data globally for later use
    currentStudentData = studentData;
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('studentRegistrationModal'));
    modal.hide();
    
    // Reset form
    form.reset();
    
    // Display student details on the left side (without saving to database)
    displayStudentDetails(null, studentData, status, amount, tempRegistrationCode);
    
    // Update the main form status and amount
    document.getElementById('status').value = status;
    document.getElementById('amount').value = amount;
}

function displayStudentDetails(registrationId, studentData, status, amount, registrationCode) {
    // Create or update the student details section
    let detailsHtml = `
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Student Details Preview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Registration Details:</strong></h6>
                        <p><strong>Registration Code:</strong> ${registrationCode}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${status === 'paid' ? 'success' : 'warning'}">${status.toUpperCase()}</span></p>
                        <p><strong>Amount:</strong> ₹${amount}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Student Information:</strong></h6>
    `;
    
    // Add student data fields
    Object.keys(studentData).forEach(fieldId => {
        const value = studentData[fieldId];
        if (value) {
            // Get field label from the form
            const fieldElement = document.querySelector(`input[name="student_data[${fieldId}]"], select[name="student_data[${fieldId}]"], textarea[name="student_data[${fieldId}]"]`);
            if (fieldElement) {
                const label = fieldElement.previousElementSibling ? 
                    fieldElement.previousElementSibling.textContent.replace('*', '').trim() : 
                    'Field ' + fieldId;
                detailsHtml += `<p><strong>${label}:</strong> ${value}</p>`;
            }
        }
    });
    
    detailsHtml += `
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success" onclick="createRegistration()">
                        <i class="fas fa-save"></i> Create Registration
                    </button>
                    <button class="btn btn-secondary ms-2" onclick="clearStudentDetails()">
                        <i class="fas fa-times"></i> Clear Details
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Insert or update the details section
    const existingDetails = document.getElementById('studentDetailsSection');
    if (existingDetails) {
        existingDetails.innerHTML = detailsHtml;
    } else {
        const container = document.createElement('div');
        container.id = 'studentDetailsSection';
        container.innerHTML = detailsHtml;
        document.getElementById('dynamicFormContainer').appendChild(container);
    }
}

// Global variable to store student data
let currentStudentData = null;

function createRegistration() {
    // Get the student details
    const detailsSection = document.getElementById('studentDetailsSection');
    if (!detailsSection || !currentStudentData) {
        alert('No student details found. Please add a student first.');
        return;
    }
    
    // Get form data
    const eventId = document.getElementById('event_id').value;
    const status = document.getElementById('status').value;
    const amount = document.getElementById('amount').value;
    const paymentId = document.getElementById('payment_id').value;
    
    if (!eventId) {
        alert('Please select an event first');
        return;
    }
    
    // Show loading
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    button.disabled = true;
    
    // Prepare registration data in the same format as saveStudentRegistration
    const registrationData = [];
    Object.keys(currentStudentData).forEach(fieldId => {
        const value = currentStudentData[fieldId];
        if (value) {
            // Get field details from the form
            const fieldElement = document.querySelector(`input[name="student_data[${fieldId}]"], select[name="student_data[${fieldId}]"], textarea[name="student_data[${fieldId}]"]`);
            if (fieldElement) {
                const label = fieldElement.previousElementSibling ? 
                    fieldElement.previousElementSibling.textContent.replace('*', '').trim() : 
                    'Field ' + fieldId;
                
                registrationData.push({
                    field_id: fieldId,
                    label: label,
                    type: fieldElement.type || 'text',
                    value: value
                });
            }
        }
    });
    
    // Send registration data
    fetch(`/admin/events/${eventId}/store-registration`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            event_id: eventId,
            registration_data: registrationData,
            status: status,
            amount: parseFloat(amount),
            payment_id: paymentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Registration created successfully!\nRegistration Code: ${data.registration_code}`);
            // Redirect to registration page
            window.location.href = '{{ route("admin.registrations") }}';
        } else {
            alert('Error creating registration: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating registration: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearStudentDetails() {
    const detailsSection = document.getElementById('studentDetailsSection');
    if (detailsSection) {
        detailsSection.remove();
    }
    // Clear global student data
    currentStudentData = null;
}

// Add event listeners to update student details when status or amount changes
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const amountInput = document.getElementById('amount');
    
    if (statusSelect) {
        statusSelect.addEventListener('change', updateStudentDetailsPreview);
    }
    
    if (amountInput) {
        amountInput.addEventListener('input', updateStudentDetailsPreview);
    }
});

function updateStudentDetailsPreview() {
    if (currentStudentData) {
        const status = document.getElementById('status').value;
        const amount = document.getElementById('amount').value || 0;
        const tempRegistrationCode = 'TEMP' + Date.now();
        
        // Update the display with new status and amount
        displayStudentDetails(null, currentStudentData, status, amount, tempRegistrationCode);
    }
}

function copyStudentDataToMainForm(studentData) {
    // This function copies the student data to the main registration form
    // You can implement this based on your needs
    console.log('Student data to copy:', studentData);
    
    // For now, just log the data
    // You can implement the actual copying logic here
}
</script>

@include('admin.layout.footer')
