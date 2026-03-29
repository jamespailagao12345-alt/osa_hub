<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\EventParticipant;
use App\Models\StudentPoint;
use Illuminate\Support\Facades\Log;

class EventFeedbackController extends Controller
{
    /**
     * Show feedback submission form for an event
     */
    public function create($eventId)
    {
        $user = auth()->user();
        $event = Event::findOrFail($eventId);

        // Check if student participated in this event
        $participation = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participation) {
            return back()->with('error', 'You did not participate in this event.');
        }

        // Check if feedback already submitted
        $existingFeedback = EventFeedback::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingFeedback) {
            return back()->with('info', 'You have already submitted feedback for this event.');
        }

        return view('student.event-feedback', compact('event', 'participation'));
    }

    /**
     * Store feedback submission
     */
    public function store(Request $request, $eventId)
    {
        $user = auth()->user();
        $event = Event::findOrFail($eventId);

        // Check if student participated in this event
        $participation = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$participation) {
            return back()->with('error', 'You did not participate in this event.');
        }

        // Check if feedback already submitted
        $existingFeedback = EventFeedback::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingFeedback) {
            return back()->with('info', 'You have already submitted feedback for this event.');
        }

        $request->validate([
            'feedback_text' => 'required|string|min:10|max:2000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        // Create feedback
        $feedback = EventFeedback::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'feedback_text' => $request->feedback_text,
            'rating' => $request->rating,
            'points_awarded' => false,
            'submitted_at' => now(),
        ]);

        // Award points if event has points assigned
        if ($event->points && $event->points > 0) {
            StudentPoint::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'feedback_id' => $feedback->id,
                'points' => $event->points,
                'notes' => "Points awarded for submitting feedback for event: {$event->name}",
                'awarded_at' => now(),
            ]);

            // Mark feedback as having points awarded
            $feedback->update(['points_awarded' => true]);

            Log::info('Points awarded for feedback', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'points' => $event->points,
            ]);
        }

        return redirect()->route('student.dashboard')
            ->with('success', 'Feedback submitted successfully!' . ($event->points ? " You earned {$event->points} points." : ''));
    }

    /**
     * Show list of events student can submit feedback for
     */
    public function index()
    {
        $user = auth()->user();

        // Get events where student participated but hasn't submitted feedback
        $participatedEvents = EventParticipant::where('user_id', $user->id)
            ->with(['event' => function($query) {
                $query->where('status', 'approved');
            }])
            ->whereHas('event', function($query) {
                $query->where('status', 'approved');
            })
            ->get()
            ->filter(function($participation) use ($user) {
                // Check if feedback already submitted
                $hasFeedback = EventFeedback::where('event_id', $participation->event_id)
                    ->where('user_id', $user->id)
                    ->exists();
                return !$hasFeedback;
            })
            ->map(function($participation) {
                return $participation->event;
            })
            ->filter()
            ->sortByDesc('start_time');

        // Get events where feedback was submitted
        $feedbackSubmitted = EventFeedback::where('user_id', $user->id)
            ->with('event')
            ->get()
            ->map(function($feedback) {
                return $feedback->event;
            })
            ->filter()
            ->sortByDesc('start_time');

        return view('student.event-feedback-list', compact('participatedEvents', 'feedbackSubmitted'));
    }
}
