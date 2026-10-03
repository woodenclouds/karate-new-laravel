@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<main class="page-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Form Builder for: {{ $event->title }}</h4>
            <a href="{{ route('admin.event') }}" class="btn btn-secondary">← Back to Events</a>
        </div>

        <form action="{{ route('admin.event.form.save', $event->id) }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="form-label">Form Title</label>
                <input type="text" name="form_title" class="form-control" value="{{ $event->form->title ?? '' }}" required>
            </div>

            <hr>
            <h5>Fields</h5>
            <div id="form-fields-container">
                @if($event->form && $event->form->formFields->count())
                    @foreach($event->form->formFields as $index => $field)
                    <div class="field-group border p-3 mb-3" id="field-group-{{ $index }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label>Label</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeField({{ $index }})">Delete</button>
                        </div>

                        <input type="text" name="fields[{{ $index }}][label]" class="form-control mb-2" value="{{ $field->label }}" required>
                        
                        <div class="mb-2">
                            <label>Field Type</label>
                            <select name="fields[{{ $index }}][type]" class="form-select field-type-select" data-index="{{ $index }}" required onchange="toggleOptions({{ $index }}, this.value)">
                                <option value="text" {{ $field->type == 'text' ? 'selected' : '' }}>Text</option>
                                <option value="email" {{ $field->type == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="number" {{ $field->type == 'number' ? 'selected' : '' }}>Number</option>
                                <option value="textarea" {{ $field->type == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                <option value="select" {{ $field->type == 'select' ? 'selected' : '' }}>Select</option>
                                <option value="radio" {{ $field->type == 'radio' ? 'selected' : '' }}>Radio</option>
                                <option value="checkbox" {{ $field->type == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                <option value="file" {{ $field->type == 'file' ? 'selected' : '' }}>File</option>
                                <option value="belt" {{ $field->type == 'belt' ? 'selected' : '' }}>Belt</option>
                                <option value="tel" {{ $field->type == 'tel' ? 'selected' : '' }}>Phone</option>
                            </select>
                        </div>
                    
                        <div class="mb-2 options-box" id="options-box-{{ $index }}" style="{{ in_array($field->type, ['select','radio','checkbox']) ? '' : 'display:none;' }}">
                            <label>Options <small>(Comma-separated)</small></label>
                            <input type="text" name="fields[{{ $index }}][options]" class="form-control" value="{{ $field->options ? implode(',', json_decode($field->options)) : '' }}">
                        </div>
                    
                        <div class="form-check">
                            <input type="checkbox" name="fields[{{ $index }}][required]" class="form-check-input" {{ $field->required ? 'checked' : '' }}>
                            <label class="form-check-label">Required</label>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            <button type="button" class="btn btn-outline-primary mb-3" id="add-form-field">+ Add New Field</button>

            <div class="mt-3">
                <button class="btn btn-success">Save Form</button>
            </div>
        </form>
    </div>
</main>

<script>
    let fieldIndex = {{ $event->form && $event->form->formFields->count() ? $event->form->formFields->count() : 0 }};
    
    document.getElementById('add-form-field').addEventListener('click', function () {
        const container = document.getElementById('form-fields-container');
        const html = `
            <div class="field-group border p-3 mb-3" id="field-group-${fieldIndex}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label>Label</label>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeField(${fieldIndex})">Delete</button>
                </div>

                <input type="text" name="fields[${fieldIndex}][label]" class="form-control mb-2" required>
    
                <div class="mb-2">
                    <label>Field Type</label>
                    <select name="fields[${fieldIndex}][type]" class="form-select field-type-select" data-index="${fieldIndex}" required onchange="toggleOptions(${fieldIndex}, this.value)">
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="textarea">Textarea</option>
                        <option value="select">Select</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="file">File</option>
                        <option value="belt">Belt</option>
                        <option value="tel">Phone</option>
                    </select>
                </div>
    
                <div class="mb-2 options-box" id="options-box-${fieldIndex}" style="display:none;">
                    <label>Options <small>(Comma-separated)</small></label>
                    <input type="text" name="fields[${fieldIndex}][options]" class="form-control" placeholder="e.g. Option 1, Option 2">
                </div>
    
                <div class="form-check">
                    <input type="checkbox" name="fields[${fieldIndex}][required]" class="form-check-input" checked>
                    <label class="form-check-label">Required</label>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        fieldIndex++;
    });
    
    function toggleOptions(index, value) {
        const optionsBox = document.getElementById(`options-box-${index}`);
        if (['select', 'radio', 'checkbox'].includes(value)) {
            optionsBox.style.display = 'block';
        } else {
            optionsBox.style.display = 'none';
        }
    }

    function removeField(index) {
        const fieldGroup = document.getElementById(`field-group-${index}`);
        if (fieldGroup) {
            fieldGroup.remove();
        }
    }
</script>

@include('admin.layout.footer')
