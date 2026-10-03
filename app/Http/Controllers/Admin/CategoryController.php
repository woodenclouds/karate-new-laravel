<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Category;
use App\Models\Admin\Form;

class CategoryController extends Controller
{
    /**
     * Display all categories with their forms and fields.
     */
    public function index()
    {
        $categories = Category::with(['form.formFields' => function ($q) {
            $q->orderBy('order');
        }])->latest()->get();

        return view('admin.category', compact('categories'));
    }

    /**
     * Store a new category and optional form.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'name' => $request->name,
        ]);

        if ($request->has('fields')) {
            $form = new Form();
            $form->category_id = $category->id;
            $form->title = $request->form_title ?? $request->name . ' Form'; // Save proper title
            $form->save();

            foreach ($request->fields as $index => $field) {
                $form->formFields()->create([
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'required' => isset($field['required']),
                    'options' => !empty($field['options']) ? json_encode(explode(',', $field['options'])) : null,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.category')->with('success', 'Category created successfully.');
    }

    /**
     * Update category name only.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
        ]);

        // Optionally update the form title as well
        if ($category->form) {
            $category->form->update([
                'title' => $request->name . ' Form',
            ]);
        }

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category and related forms/fields.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->form) {
            $category->form->formFields()->delete();
            $category->form->delete();
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }

    /**
     * Save form fields and title for a given category.
     */
    public function saveForm(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $form = $category->form ?? new Form();
        $form->category_id = $category->id;
        $form->title = $request->form_title ?? $category->name . ' Form'; // Ensures title always aligns
        $form->save();

        // Delete previous fields
        $form->formFields()->delete();

        foreach ($request->fields ?? [] as $index => $field) {
            $form->formFields()->create([
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => isset($field['required']),
                'options' => !empty($field['options']) ? json_encode(explode(',', $field['options'])) : null,
                'order' => $index,
            ]);
        }

        return redirect()->route('admin.category')->with('success', 'Form fields saved successfully.');
    }

    public function getBeltOptions()
    {
        $belts = \DB::table('tbl_belt')
            ->orderBy('id', 'asc')
            ->pluck('from_belt');

        return response()->json($belts);
    }
}
