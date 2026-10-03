<?php

namespace App\Http\Controllers\Admin;
use App\Models\Message;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $messages = \App\Models\Message::latest()->get();
        return view('admin.message', compact('messages'));
    }

    public function destroy($id)
    {
        \App\Models\Message::findOrFail($id)->delete();
        return back()->with('success', 'Message deleted successfully.');
    }   
    
    public function submitMessage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        Message::create($request->all());

        return back()->with('success', 'Message sent successfully.');
    }
}
