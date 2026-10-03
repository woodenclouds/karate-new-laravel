@include('user.layout.header')
@include('user.layout.navbar')

<!-- Bootstrap 5 CDN if not already included -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: linear-gradient(135deg, #000000, #3a0000);
        /* min-height: 100vh; */
        margin: 0;
        padding-top: 130px;
        font-family: 'Segoe UI', sans-serif;
        color: #fff;
    }

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

<div class="container" style="padding-bottom:130px;">
    <div class="form-section">
        <!-- Event Details -->
        <div class="event-details">
            @php
                $categoryName = strtolower(trim($event->category->name ?? ''));
            @endphp

            @if ($categoryName === 'competition')
                <h2 class="section-title">MENTORS SPORTS KARATE - DO</h2>
                <p><strong>Affiliated with:</strong> Kerala Karate Association & Indian Karate Federation</p>
                <p><strong>Recognized by:</strong> World Karate Federation, Asian Karate Federation,<br> and
                    International
                    Olympic Committee, Karate Association of India</p>
                <br>
                <h2 style="font-family:'Times New Roman', Times, serif;font-size:18px;color:black;">PARTICIPANT
                    INDIVIDUAL ENTRY FORM</h2>
                <div class="instructions" style="text-align: left; margin-top: 10px;color:#3a0000">
                    <p><strong>Instructions:</strong></p>
                    <ul style="padding-left: 20px;font-size:13px;">
                        <li>Please fill in all required fields carefully.</li>
                        <li>Ensure the spelling of your name matches your school ID records.</li>
                        <li>Select your correct belt level (if applicable).</li>
                        <li>Entries once submitted cannot be modified.</li>
                        <li>Registration Fee: <strong>₹{{ $event->fee }}</strong> (per participant).</li>
                        <li>If competing as a team member, an additional fee of
                            <strong>₹{{ $event->additional_fee }}</strong> applies.
                        </li>

                    </ul>
                </div>
            @elseif (str_contains($categoryName, 'kyu'))
                <h2 class="section-title">MENTORS SPORTS KARATE - DO</h2>
                <p><strong>Affiliated with:</strong> Kerala Karate Association & Indian Karate Federation</p>
                <p><strong>Recognized by:</strong> World Karate Federation, Asian Karate Federation, and
                    <br>International
                    Olympic Committee, Karate Association of India
                </p><br>
                <p><strong>Registration for KYU Grading</strong></p>
                <p>After completing registration, you must pay the applicable belt fee before your submission is
                    accepted. The exact fee will be displayed once you select your current belt, and you will be
                    redirected to the secure payment gateway to complete the process.</p>
            @else
                <h2 class="section-title">{{ $form->title ?? 'Registration Form' }}</h2>
            @endif
        </div>


        <!-- Form Starts -->
        <form action="{{ route('user.form.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">

            <div class="row g-4">
                @foreach ($form->formFields->sortBy('order') as $field)
                    @php
                        $fieldName = "fields[{$field->id}]";
                        $label = $field->label;
                        $required = $field->required ? 'required' : '';
                        $options = $field->options ? json_decode($field->options) : [];
                        $type = $field->type;
                        $inputType = $type;

                        $validationAttrs = '';
                        if (Str::contains(strtolower($label), 'contact')) {
                            $inputType = 'tel';
                            $validationAttrs =
                                'pattern="[0-9]{10}" maxlength="10" title="Enter a 10-digit phone number"';
                        }
                        if ($type === 'email') {
                            $validationAttrs =
                                'pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Enter a valid email address"';
                        }
                        // if (Str::contains(strtolower($label), 'declaration')) {
                        //     $required = 'required';
                        // }
                    @endphp

                    <div class="col-md-6">
                        <label class="form-label">{{ $label }}</label>

                        @if (in_array($type, ['text', 'email', 'number', 'tel']))
                            <input type="{{ $type }}" name="{{ $fieldName }}" class="form-control"
                                {{ $required }} {!! $validationAttrs !!}>
                        @elseif($type == 'date')
                            <input type="text" id="dob" name="{{ $fieldName }}" class="form-control"
                                placeholder="dd-mm-yyyy" {{ $required }} {!! $validationAttrs !!}>
                        @elseif($type == 'textarea')
                            <textarea name="{{ $fieldName }}" class="form-control" rows="3" {{ $required }}></textarea>
                        @elseif(in_array($type, ['select', 'belt']))
                            <select name="{{ $fieldName }}" id="belt_select_{{ $field->id }}"
                                class="form-select" {{ $required }}>
                                <option value="">-- Select--</option>

                                @if ($type == 'belt')
                                    @php
                                        $belts = \App\Models\Admin\belt::select('id', 'from_belt', 'to_belt', 'fees')
                                            ->orderBy('id', 'asc')
                                            ->get();

                                        // If category is KYU, exclude Black Belt
                                        if (str_contains($categoryName, 'kyu')) {
                                            $belts = $belts->filter(function ($belt) {
                                                return !str_contains(strtolower($belt->from_belt), 'black');
                                            });
                                        }
                                    @endphp

                                    @foreach ($belts as $belt)
                                        @php
                                            $overrideFee = isset($eventBeltFeeMap) && $eventBeltFeeMap->has($belt->id)
                                                ? $eventBeltFeeMap[$belt->id]
                                                : $belt->fees;
                                        @endphp
                                        <option value="{{ $belt->id }}" 
                                            data-fee="{{ $overrideFee }}" 
                                            data-from-belt="{{ $belt->from_belt }}" 
                                            data-to-belt="{{ $belt->to_belt }}">
                                            {{ $belt->from_belt }}
                                        </option>
                                    @endforeach
                                @else
                                    @foreach ($options as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                @endif
                            </select>

                            {{-- Fee Display for belt fields --}}
                            @if ($type == 'belt')
                                <p id="belt_fee_{{ $field->id }}"
                                    style="margin-top:10px; font-weight:bold; color:#444; display:none;">
                                    {{-- Fee will be displayed here when belt is selected --}}
                                </p>
                            @endif
                        @elseif($type == 'radio')
                            <div class="d-flex flex-wrap gap-3 pt-2">
                                @foreach ($options as $option)
                                    <div class="form-check">
                                        <input type="radio" name="{{ $fieldName }}" value="{{ $option }}"
                                            class="form-check-input" {{ $required }}>
                                        <label class="form-check-label">{{ $option }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($type == 'checkbox')
                            <div class="d-flex flex-wrap gap-3 pt-2">
                                @foreach ($options as $option)
                                    <div class="form-check me-3">
                                        <input type="checkbox" name="{{ $fieldName }}[]"
                                            value="{{ $option }}" class="form-check-input"
                                            id="{{ $fieldName }}_{{ $loop->index }}" required>
                                        <label class="form-check-label"
                                            for="{{ $fieldName }}_{{ $loop->index }}">{{ $option }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($type == 'file')
                            <input type="file" name="{{ $fieldName }}" class="form-control" {{ $required }}>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="submit-btn-wrapper">
                <button type="submit" class="btn btn-next mt-4">Proceed to Pay</button>
            </div>
        </form>
    </div>
</div>


<script>
    form.addEventListener('submit', function(e) {
        let hasError = false;

        // Phone number validation
        phoneInputs.forEach(input => {
            const value = input.value.trim();
            const errorElement = input.parentElement.querySelector('.phone-error');
            if (!/^\d{10}$/.test(value)) {
                errorElement.classList.remove('d-none');
                hasError = true;
            } else {
                errorElement.classList.add('d-none');
            }
        });

        // Optional: Date field check for future dates
        const dateInputs = form.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            const selectedDate = new Date(input.value);
            const today = new Date();
            const errorElement = input.nextElementSibling;
            if (selectedDate > today) {
                if (!errorElement || !errorElement.classList.contains('date-error')) {
                    input.insertAdjacentHTML('afterend',
                        `<small class="text-danger date-error">Date cannot be in the future</small>`
                    );
                }
                hasError = true;
            } else {
                if (errorElement && errorElement.classList.contains('date-error')) {
                    errorElement.remove();
                }
            }
        });

        if (hasError) e.preventDefault();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Get ALL belt dropdowns
        let beltSelects = document.querySelectorAll("[id^='belt_select_']");

        beltSelects.forEach(function(beltSelect) {
            let fieldId = beltSelect.id.replace("belt_select_", "");
            let feeDisplay = document.getElementById("belt_fee_" + fieldId);

            if (!feeDisplay) return;

            beltSelect.addEventListener("change", function() {
                let selectedOption = beltSelect.options[beltSelect.selectedIndex];
                let fee = selectedOption.getAttribute("data-fee");
                let fromBelt = selectedOption.getAttribute("data-from-belt");
                let toBelt = selectedOption.getAttribute("data-to-belt");

                if (fee && fromBelt && toBelt) {
                    feeDisplay.textContent = "From " + fromBelt + " to " + toBelt + " - ₹" + parseFloat(fee).toFixed(2);
                    feeDisplay.style.display = "block";
                } else {
                    feeDisplay.textContent = "";
                    feeDisplay.style.display = "none";
                }
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script>
    flatpickr("#dob", {
        dateFormat: "d-m-Y" // shows in dd-mm-yyyy format
    });
</script>

@include('user.layout.footer')
