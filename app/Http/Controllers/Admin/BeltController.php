<?php

// app/Http/Controllers/Admin/BeltController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\belt;

class BeltController extends Controller
{

    public function index(Request $request)
    {
        $beltsQuery = Belt::query();
    
        if ($request->ajax()) {
            if ($request->has('search') && $request->search != '') {
                $beltsQuery->where('from_belt', 'like', '%' . $request->search . '%')
                    ->orWhere('to_belt', 'like', '%' . $request->search . '%');
            }
    
            $belts = $beltsQuery->orderBy('id', 'ASC')->get(); // Order applied
            $rows = '';
            foreach ($belts as $belt) {
                $rows .= '
                    <tr>
                        <td>' . $belt->id . '</td>
                        <td>' . $belt->from_belt . '</td>
                        <td>' . $belt->to_belt . '</td>
                        <td>' . $belt->fees . '</td>
                        <td>
                            <a href="' . route('admin.belt.update', $belt->id) . '" class="text-warning">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="' . route('admin.belt.delete', $belt->id) . '" method="POST" style="display:inline;">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn p-0 text-danger">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>';
            }
    
            return response()->json(['html' => $rows]);
        }
    
        // Apply ordering for initial page load
        $belts = $beltsQuery->orderBy('id', 'ASC')->get();
    
        return view('admin.belt', compact('belts'));
    }

                                                                                                  


    public function store(Request $request)
    {
        $request->validate([
            'from_belt' => 'required|string',
            'to_belt' => 'required|string',
            'fees' => 'required|numeric|min:0',
        ]);

        Belt::create([
            'from_belt' => $request->from_belt,
            'to_belt' => $request->to_belt,
            'fees' => $request->fees,
        ]);

        return redirect()->back()->with('success', 'Belt data saved successfully.');
    }


    public function update(Request $request, $id)
    {
        // Validate the form data
        $request->validate([
            'from_belt' => 'required|string|max:255',
            'to_belt'   => 'required|string|max:255',
            'fees'      => 'required|numeric|min:0',
        ]);

        // Find the belt record
        $belt = Belt::findOrFail($id);

        // Update the record
        $belt->from_belt = $request->from_belt;
        $belt->to_belt = $request->to_belt;
        $belt->fees = $request->fees;
        $belt->save();

        // Redirect with success message
        return redirect()->route('admin.belt')->with('success', 'Belt updated successfully.');
    }

    public function destroy($id)
    {
        $belt = Belt::findOrFail($id);
        $belt->delete();

        return redirect()->route('admin.belt')->with('success', 'Belt deleted successfully.');
    }

    public function getBeltName($id)
    {
        $belt = belt::find($id);
        if ($belt) {
            return response()->json(['name' => $belt->from_belt]);
        }
        return response()->json(['name' => null], 404);
    }

}