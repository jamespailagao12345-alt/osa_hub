<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ParticipantExport;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\EventParticipant::with(['user', 'event'])
            ->whereHas('event', fn($q) => $q->where('created_by', auth()->id()));

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('course_id')) {
            $query->whereHas('user', fn($q) => $q->where('course_id', $request->course_id));
        }
        if ($request->filled('year_level')) {
            $query->whereHas('user.studentInformation', fn($q) => $q->where('year_level', $request->year_level));
        }

        // Get all participants, sort alphabetically, then paginate
        $allParticipants = $query->get()->sortBy(function($participant) {
            $lastName = strtolower($participant->user->last_name ?? '');
            $firstName = strtolower($participant->user->first_name ?? '');
            return $lastName . ' ' . $firstName;
        })->values();
        
        // Manually paginate the sorted collection
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $total = $allParticipants->count();
        $offset = ($currentPage - 1) * $perPage;
        $paginatedItems = $allParticipants->slice($offset, $perPage)->values();
        $participants = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $events = \App\Models\Event::where('created_by', auth()->id())->get();
        $departments = \App\Models\Department::all();

        return view('staff.participants', compact('participants', 'events', 'departments'));
    }

    public function export(Request $request)
    {
        // Reuse filtering logic
        $query = \App\Models\EventParticipant::with(['user', 'event'])
            ->whereHas('event', fn($q) => $q->where('created_by', auth()->id()));

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('course_id')) {
            $query->whereHas('user', fn($q) => $q->where('course_id', $request->course_id));
        }
        if ($request->filled('year_level')) {
            $query->whereHas('user.studentInformation', fn($q) => $q->where('year_level', $request->year_level));
        }

        $participants = $query->get()->sortBy(function($participant) {
            $lastName = strtolower($participant->user->last_name ?? '');
            $firstName = strtolower($participant->user->first_name ?? '');
            return $lastName . ' ' . $firstName;
        })->values();

        // Only admin can export
        if (auth()->user()->role !== 4) {
            abort(403, 'Export is admin-only.');
        }

        return Excel::download(new ParticipantExport($participants), 'osa_participants.xlsx');
    }

    public function history(Request $request)
    {
        $query = \App\Models\EventParticipant::with(['user', 'event'])
            ->whereHas('event', fn($q) => $q->where('created_by', auth()->id()));
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('course_id')) {
            $query->whereHas('user', fn($q) => $q->where('course_id', $request->course_id));
        }
        if ($request->filled('year_level')) {
            $query->whereHas('user.studentInformation', fn($q) => $q->where('year_level', $request->year_level));
        }
        $participants = $query->latest()->paginate(20);
        $events = \App\Models\Event::where('created_by', auth()->id())->get();
        $departments = \App\Models\Department::all();
        $courses = \App\Models\Course::all();
        return view('staff.participants-history', compact('participants', 'events', 'departments', 'courses'));
    }
}