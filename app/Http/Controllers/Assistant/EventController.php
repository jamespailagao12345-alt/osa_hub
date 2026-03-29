<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        // List all events for student leaders (own, department, courses)
        // $events = ...
        return view('assistant.events.index');
    }

    public function created()
    {
        // List events created by this student leader
        // $createdEvents = ...
        return view('assistant.events.created');
    }

    public function requirements($id)
    {
        // Show required files for event creation
        // $requirements = ...
        return view('assistant.events.requirements');
    }

    public function create()
    {
        // Show event creation form
        return view('assistant.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
        ]);
        // Save event (replace with your model logic)
        $event = new \App\Models\Event();
        $event->name = $validated['name'];
        $event->description = $validated['description'];
        $event->event_date = $validated['event_date'];
        $event->created_by = auth()->id();
        $event->save();
        return redirect()->route('student-leader.events.index')->with('success', 'Event created successfully.');
    }

    public function calendar()
    {
        // Show read-only calendar of events
        // $calendarEvents = ...
        return view('assistant.events.calendar');
    }
}
