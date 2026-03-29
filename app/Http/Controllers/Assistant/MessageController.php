<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        // Show messages between student leaders and assigned staff
        // $messages = ...
        return view('assistant.messages.index');
    }

    public function send(Request $request)
    {
        // Send message to assigned staff or other student leaders
        // $recipientId = $request->input('recipient_id');
        // $content = $request->input('content');
        // ...
        return back()->with('success', 'Message sent.');
    }
}
