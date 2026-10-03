@include('admin.layout.header')
@include('admin.layout.navbar')
@include('admin.layout.sidebar')

<main class="page-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Categories</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add
                Category</button>
        </div>

        <!-- Category Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Form Fields</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $key => $category)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    @if ($category->form && $category->form->formFields->count())
                                        {{ $category->form->formFields->count() }} field(s)
                                    @else
                                        No fields
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#editCategoryModal-{{ $category->id }}">Edit</a>
                                    <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#formBuilderModal-{{ $category->id }}">Form Builder</a>
                                    <form action="{{ route('admin.category.delete', $category->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Delete this category?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Edit and Form Builder Modals --}}
        @foreach ($categories as $category)
            {{-- Edit Category Modal --}}
            <div class="modal fade" id="editCategoryModal-{{ $category->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form action="{{ route('admin.category.update', $category->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="text" name="name" class="form-control" value="{{ $category->name }}"
                                    required>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary" type="submit">Update</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Builder Modal --}}
            <div class="modal fade" id="formBuilderModal-{{ $category->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('admin.category.form.save', $category->id) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Form Builder - {{ $category->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Form Title</label>
                                    <input type="text" name="form_title" class="form-control"
                                        value="{{ $category->form->title ?? $category->name . ' Form' }}" required>
                                </div>
                                <div class="row" id="form-fields-container-{{ $category->id }}">
                                    @php $index = 0; @endphp
                                    @foreach ($category->form->formFields ?? [] as $field)
                                        <div class="form-field-item col-md-6 mb-2 border rounded p-2 position-relative">
                                            <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                onclick="this.closest('.form-field-item').remove()">×</button>

                                            <input type="text" name="fields[{{ $index }}][label]"
                                                value="{{ $field->label }}" class="form-control mb-1"
                                                placeholder="Label" required>
                                            <select name="fields[{{ $index }}][type]"
                                                class="form-select mb-1 field-type-select"
                                                data-index="{{ $index }}"
                                                onchange="handleFieldTypeChange(this)">
                                                <option value="text" {{ $field->type == 'text' ? 'selected' : '' }}>
                                                    Text</option>
                                                <option value="email" {{ $field->type == 'email' ? 'selected' : '' }}>
                                                    Email</option>
                                                <option value="number"
                                                    {{ $field->type == 'number' ? 'selected' : '' }}>Number</option>
                                                <option value="select"
                                                    {{ $field->type == 'select' ? 'selected' : '' }}>Select</option>
                                                <option value="radio" {{ $field->type == 'radio' ? 'selected' : '' }}>
                                                    Radio</option>
                                                <option value="checkbox"
                                                    {{ $field->type == 'checkbox' ? 'selected' : '' }}>Checkbox
                                                </option>
                                                <option value="file" {{ $field->type == 'file' ? 'selected' : '' }}>
                                                    File</option>
                                                <option value="belt" {{ $field->type == 'belt' ? 'selected' : '' }}>
                                                    Belt</option>
                                                <option value="date" {{ $field->type == 'date' ? 'selected' : '' }}>
                                                    Date</option>
                                                <option value="tel" {{ $field->type == 'tel' ? 'selected' : '' }}>
                                                    Tel</option>
                                            </select>
                                            <input type="text" name="fields[{{ $index }}][options]"
                                                class="form-control mb-1" placeholder="Options (comma-separated)"
                                                value="{{ implode(',', json_decode($field->options ?? '[]', true)) }}">
                                            <div class="form-check">
                                                <input type="checkbox" name="fields[{{ $index }}][required]"
                                                    class="form-check-input" {{ $field->required ? 'checked' : '' }}>
                                                <label class="form-check-label">Required</label>
                                            </div>
                                        </div>

                                        @php $index++; @endphp
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                                    onclick="addFormField({{ $category->id }}, {{ $index }})">+ Add
                                    Field</button>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-success" type="submit">Save Fields</button>
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</main>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.category.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="mb-3 col-12">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Category Name"
                            required>
                    </div>
                    <div class="mb-3 col-12">
                        <label class="form-label">Form Title</label>
                        <input type="text" name="form_title" class="form-control" placeholder="Enter form title">
                    </div>
                    <hr class="mt-2">
                    <h6>Optional: Add Form Fields</h6>
                    <div id="form-fields-container-add" class="row"></div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFormFieldAdd()">+
                            Add Another Field</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('admin.layout.footer')

<script>
    let addFieldIndex = 0;
    const formFieldCounters = {};

    function addFormFieldAdd() {
        const container = document.getElementById('form-fields-container-add');
        const html = getFieldHtml(addFieldIndex, 'add');
        container.insertAdjacentHTML('beforeend', html);
        addFieldIndex++;
    }

    function addFormField(categoryId, currentIndex = 0) {
        const container = document.getElementById(`form-fields-container-${categoryId}`);
        if (!formFieldCounters[categoryId]) formFieldCounters[categoryId] = currentIndex;
        const index = formFieldCounters[categoryId]++;
        const html = getFieldHtml(index, categoryId);
        container.insertAdjacentHTML('beforeend', html);
    }

    function getFieldHtml(index, id) {
        return `
    <div class="form-field-item col-md-6 mb-2 border rounded p-2 position-relative">
        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
            onclick="this.closest('.form-field-item').remove()">×</button>

        <input type="text" name="fields[${index}][label]" placeholder="Label" class="form-control mb-1" required>
        <select name="fields[${index}][type]" class="form-select mb-1 field-type-select" data-index="${index}" onchange="handleFieldTypeChange(this)">
            <option value="text">Text</option>
            <option value="email">Email</option>
            <option value="number">Number</option>
            <option value="select">Select</option>
            <option value="radio">Radio</option>
            <option value="checkbox">Checkbox</option>
            <option value="file">File</option>
            <option value="belt">Belt</option>
            <option value="date">Date</option>
            <option value="tel">Tel</option>
        </select>
        <input type="text" name="fields[${index}][options]" class="form-control mb-1" placeholder="Options (comma-separated)">
        <div class="form-check">
            <input type="checkbox" name="fields[${index}][required]" class="form-check-input">
            <label class="form-check-label">Required</label>
        </div>
    </div>
    `;
    }


    async function handleFieldTypeChange(selectElement) {
        const type = selectElement.value;
        const index = selectElement.dataset.index || 0;
        const parent = selectElement.closest('.form-field-item');
        const optionsInput = parent.querySelector(`input[name="fields[${index}][options]"]`);

        if (type === 'belt') {
            try {
                const response = await fetch('/admin/api/belts');
                const data = await response.json();
                if (optionsInput) optionsInput.value = data.join(',');
            } catch (err) {
                console.error('Error fetching belt data:', err);
            }
        } else if (type === 'file') {
            optionsInput.value = '';
            optionsInput.placeholder = 'No options needed for file';
        } else if (type === 'date') {
            optionsInput.value = '';
            optionsInput.placeholder = 'No options needed for date';
        } else {
            optionsInput.value = '';
            optionsInput.placeholder = 'Options (comma-separated)';
        }
    }
</script>
