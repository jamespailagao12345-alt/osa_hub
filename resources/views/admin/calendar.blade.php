@extends('layouts.app')

@section('title', 'Academic Calendar')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10 py-4">
            <div class="d-flex justify-content-between align-items-center mb-3" style="margin-top: 20px;">
                <h1 class="h3 mb-0" style="margin-left: 15px;"><strong>Academic Calendar AY {{ $year }}-{{ $year + 1 }}</strong></h1>
                <div>
                    @if(request()->has('return_to'))
                        @php
                            $returnUrl = request('return_to');
                            // Decode multiple times until no more encoding is found
                            $decoded = $returnUrl;
                            $previous = '';
                            while ($decoded !== $previous && strpos($decoded, '%') !== false) {
                                $previous = $decoded;
                                $decoded = urldecode($decoded);
                            }
                            $returnUrl = $decoded;
                            
                            // If it's a full URL (starts with http:// or https://), extract just the path
                            if (preg_match('/^https?:\/\//', $returnUrl)) {
                                $parsed = parse_url($returnUrl);
                                $backUrl = isset($parsed['path']) ? urldecode($parsed['path']) : '/';
                                if (isset($parsed['query'])) {
                                    $backUrl .= '?' . $parsed['query'];
                                }
                            } else {
                                $backUrl = $returnUrl;
                            }
                        @endphp
                        <a href="{{ $backUrl }}" class="btn btn-secondary">Back</a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Back</a>
                    @endif
                </div>
            </div>

            <!-- View Toggle Buttons and Add New Event -->
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group" aria-label="View Toggle">
                    <input type="radio" class="btn-check" name="viewToggle" id="calendarView" value="calendar" checked>
                    <label class="btn btn-outline-primary" for="calendarView">
                        <i class="bi bi-calendar3"></i> Calendar View
                    </label>
                    <input type="radio" class="btn-check" name="viewToggle" id="listView" value="list">
                    <label class="btn btn-outline-primary" for="listView">
                        <i class="bi bi-list-ul"></i> List View
                    </label>
                </div>
                <a href="{{ route('admin.events.create') }}" class="btn btn-outline-primary" style="height: 38px; display: inline-flex; align-items: center; padding: 0.375rem 0.75rem;">
                    <i class="bi bi-plus-circle"></i> Add New Event
                </a>
            </div>

            <!-- Calendar View -->
            <div id="calendarViewContainer" class="view-container" style="min-height: 600px;">
            <div class="row">
                <!-- Left Column: Calendar Month -->
                <div class="col-md-8">
                    @php
                        $monthColors = ['August' => 'danger', 'September' => 'success', 'October' => 'danger', 
                                       'November' => 'success', 'December' => 'info', 'January' => 'info',
                                       'February' => 'info', 'March' => 'success', 'April' => 'success', 
                                       'May' => 'success', 'June' => 'warning', 'July' => 'primary'];
                        $monthStart = $currentMonth->copy()->startOfMonth();
                        $monthEnd = $currentMonth->copy()->endOfMonth();
                        $startCell = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                        $endCell = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                        $colorClass = $monthColors[$monthName] ?? 'secondary';
                        $classDays = 0;
                    @endphp
                    
                    <div class="card mb-4">
                        <div class="card-header bg-{{ $colorClass }} text-white d-flex justify-content-between align-items-center">
                            @php
                                $prevParams = ['year' => $prevMonth->year, 'month' => $prevMonth->month];
                                $nextParams = ['year' => $nextMonth->year, 'month' => $nextMonth->month];
                                if(request()->has('return_to')) {
                                    $prevParams['return_to'] = request('return_to');
                                    $nextParams['return_to'] = request('return_to');
                                }
                            @endphp
                            <a href="{{ route('admin.calendar', $prevParams) }}" class="btn btn-sm btn-light">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <strong>{{ $monthName }} {{ $displayYear }}</strong>
                            <a href="{{ route('admin.calendar', $nextParams) }}" class="btn btn-sm btn-light">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                        <div class="card-body p-2">
                            <table class="table table-bordered table-sm mb-2" style="font-size: 0.85rem;">
                                <thead>
                                    <tr class="text-center">
                                        <th class="p-2" style="width: 14.28%;">Sun</th>
                                        <th class="p-2" style="width: 14.28%;">Mon</th>
                                        <th class="p-2" style="width: 14.28%;">Tue</th>
                                        <th class="p-2" style="width: 14.28%;">Wed</th>
                                        <th class="p-2" style="width: 14.28%;">Thu</th>
                                        <th class="p-2" style="width: 14.28%;">Fri</th>
                                        <th class="p-2" style="width: 14.28%;">Sat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($date = $startCell->copy(); $date->lte($endCell);)
                                        <tr>
                                            @for($i = 0; $i < 7; $i++)
                                                @php
                                                    $isInMonth = $date->between($monthStart, $monthEnd);
                                                    $isWeekend = in_array($date->dayOfWeek, [0, 6]);
                                                    $dateKey = $date->format('Y-m-d');
                                                    $dayEvents = $eventsByDate->get($dateKey, collect());
                                                    $isHoliday = $dayEvents->isNotEmpty();
                                                    if ($isInMonth && !$isWeekend && !$isHoliday) {
                                                        $classDays++;
                                                    }
                                                @endphp
                                                <td class="p-2 text-center align-middle calendar-cell {{ $isInMonth ? 'in-month' : 'out-month' }}" 
                                                    style="height: 60px; cursor: {{ $isInMonth ? 'pointer' : 'default' }}; {{ !$isInMonth ? 'background-color: #f8f9fa;' : '' }}"
                                                    @if($isInMonth)
                                                        onclick="window.location.href='{{ route('admin.events.create', ['date' => $dateKey]) }}'"
                                                        title="Click to add event on {{ $date->format('M d, Y') }}"
                                                    @endif>
                                                    <div class="d-flex flex-column h-100 justify-content-between">
                                                        <div class="date-number text-{{ !$isInMonth ? 'muted' : ($isHoliday ? 'danger fw-bold' : 'dark') }}" style="font-size: 0.95rem;">
                                                            {{ $date->day }}
                                                        </div>
                                                        @if($isInMonth)
                                                            <div class="date-marker" style="font-size: 0.75rem; line-height: 1; min-height: 12px;">
                                                                @if($isWeekend && !$isHoliday)
                                                                    <span class="text-danger fw-bold">X</span>
                                                                @elseif($isHoliday)
                                                                    <span class="text-danger fw-bold" title="{{ $dayEvents->pluck('name')->implode(', ') }}">
                                                                        @if($dayEvents->count() == 1)
                                                                            <a href="{{ route('admin.events.edit', $dayEvents->first()->id) }}" class="text-danger text-decoration-none" style="cursor: pointer;" onclick="event.stopPropagation();">
                                                                                {{ \Illuminate\Support\Str::limit($dayEvents->first()->name, 10) }}
                                                                            </a>
                                                                        @else
                                                                            <a href="{{ route('admin.events.edit', $dayEvents->first()->id) }}" class="text-danger text-decoration-none" style="cursor: pointer;" onclick="event.stopPropagation();" title="Click to edit first event. {{ $dayEvents->count() }} events on this day.">
                                                                                {{ $dayEvents->count() }} events
                                                                            </a>
                                                                        @endif
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                @php $date->addDay(); @endphp
                                            @endfor
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                            <div class="text-center">
                                <strong>{{ $monthName }} {{ $displayYear }} Class Days: {{ $classDays }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- your content here  -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <strong>Semester Dates</strong>
                        </div>
                        <div class="card-body">
                            <p><strong>First Semester {{ $year }}-{{ $year + 1 }}:</strong><br>
                            August 31, {{ $year }} - January 24, {{ $year + 1 }}</p>
                            
                            <p><strong>Second Semester {{ $year }}-{{ $year + 1 }}:</strong><br>
                            February 2, {{ $year + 1 }} - June 18, {{ $year + 1 }}</p>
                            
                            <p><strong>Mid-Year Term {{ $year + 1 }}:</strong><br>
                            July 1, {{ $year + 1 }} - August 10, {{ $year + 1 }} (6 Weeks)</p>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <strong>Holidays  {{ $year }}</strong>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            @php
                                $yearEvents = $events->filter(function($e) use ($year) {
                                    $eventYear = \Carbon\Carbon::parse($e->start_time)->year;
                                    return $eventYear == $year || $eventYear == $year + 1;
                                })->sortBy('start_time');
                                $eventsByMonth = $yearEvents->groupBy(function($e) {
                                    return \Carbon\Carbon::parse($e->start_time)->format('F Y');
                                });
                            @endphp
                            
                            @foreach($eventsByMonth as $monthYear => $monthEvents)
                                <div class="mb-3">
                                    <strong>{{ $monthYear }}</strong>
                                    <ul class="list-unstyled ms-2">
                                        @foreach($monthEvents as $ev)
                                            <li class="text-danger">
                                                <strong>{{ \Carbon\Carbon::parse($ev->start_time)->format('M d') }}:</strong> 
                                                <a href="{{ route('admin.events.edit', $ev->id) }}" class="text-danger text-decoration-none" style="cursor: pointer;">
                                                    {{ $ev->name }}
                                                </a>
                                                @if($ev->description)
                                                    <br><small class="text-muted">({{ $ev->description }})</small>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                            
                            @if($yearEvents->isEmpty())
                                <p class="text-muted">No events scheduled for this academic year.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <strong>Summary of School Days</strong>
                        </div>
                        <div class="card-body">
                            @php
                                $firstSemTotal = 0;
                                $secondSemTotal = 0;
                                foreach(['August', 'September', 'October', 'November', 'December', 'January'] as $m) {
                                    $mNum = \Carbon\Carbon::createFromFormat('F', $m)->format('n');
                                    $mYear = in_array($m, ['August', 'September', 'October', 'November', 'December']) ? $year : ($year + 1);
                                    $mStart = \Carbon\Carbon::createFromDate($mYear, $mNum, 1)->startOfMonth();
                                    $mEnd = \Carbon\Carbon::createFromDate($mYear, $mNum, 1)->endOfMonth();
                                    $days = 0;
                                    for($d = $mStart->copy(); $d->lte($mEnd); $d->addDay()) {
                                        if (!in_array($d->dayOfWeek, [0,6])) {
                                            $dKey = $d->format('Y-m-d');
                                            if (!$eventsByDate->has($dKey)) $days++;
                                        }
                                    }
                                    $firstSemTotal += $days;
                                }
                                foreach(['February', 'March', 'April', 'May', 'June'] as $m) {
                                    $mNum = \Carbon\Carbon::createFromFormat('F', $m)->format('n');
                                    $mYear = $year + 1;
                                    $mStart = \Carbon\Carbon::createFromDate($mYear, $mNum, 1)->startOfMonth();
                                    $mEnd = \Carbon\Carbon::createFromDate($mYear, $mNum, 1)->endOfMonth();
                                    $days = 0;
                                    for($d = $mStart->copy(); $d->lte($mEnd); $d->addDay()) {
                                        if (!in_array($d->dayOfWeek, [0,6])) {
                                            $dKey = $d->format('Y-m-d');
                                            if (!$eventsByDate->has($dKey)) $days++;
                                        }
                                    }
                                    $secondSemTotal += $days;
                                }
                            @endphp
                            <p><strong>First Semester Total:</strong> {{ $firstSemTotal }}</p>
                            <p><strong>Second Semester Total:</strong> {{ $secondSemTotal }}</p>
                            <p><strong>Total:</strong> {{ $firstSemTotal + $secondSemTotal }}</p>
                        </div>
                    </div>
                </div>
            </div>

            </div>
            <!-- End Calendar View -->

            <!-- List View -->
            <div id="listViewContainer" class="view-container" style="display: none; min-height: 600px;">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><strong>Academic Calendar Activities AY {{ $year }}-{{ $year + 1 }}</strong></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0" style="font-size: 0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%;">ACTIVITIES</th>
                                        <th style="width: 22%;">1st SEMESTER AY {{ $year }}-{{ $year + 1 }}</th>
                                        <th style="width: 22%;">2nd SEMESTER AY {{ $year }}-{{ $year + 1 }}</th>
                                        <th style="width: 21%;">MIDYEAR TERM {{ $year + 1 }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $highlightKeywords = ['start of classes', 'end of classes', 'commencement', 'graduation'];
                                    @endphp
                                    @forelse($eventsByActivity as $activityKey => $activity)
                                        @php
                                            $isHighlighted = false;
                                            foreach ($highlightKeywords as $keyword) {
                                                if (stripos(strtolower($activity['name']), $keyword) !== false) {
                                                    $isHighlighted = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        <tr class="{{ $isHighlighted ? 'table-warning' : '' }}">
                                            <td class="fw-bold">
                                                @php
                                                    // Get the first event from any semester to link to edit
                                                    $firstEvent = null;
                                                    if (!empty($activity['first_sem'])) {
                                                        $firstEvent = $activity['first_sem'][0]['event'];
                                                    } elseif (!empty($activity['second_sem'])) {
                                                        $firstEvent = $activity['second_sem'][0]['event'];
                                                    } elseif (!empty($activity['midyear'])) {
                                                        $firstEvent = $activity['midyear'][0]['event'];
                                                    }
                                                @endphp
                                                @if($firstEvent)
                                                    <a href="{{ route('admin.events.edit', $firstEvent->id) }}" class="text-dark text-decoration-none" style="cursor: pointer;">
                                                        {{ $activity['name'] }}
                                                    </a>
                                                @else
                                                    {{ $activity['name'] }}
                                                @endif
                                                @if($activity['description'])
                                                    <br><small class="text-muted fw-normal">{{ $activity['description'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($activity['first_sem']))
                                                    @foreach($activity['first_sem'] as $semEvent)
                                                        @php
                                                            $start = $semEvent['start'];
                                                            $end = $semEvent['end'];
                                                            $event = $semEvent['event'];
                                                        @endphp
                                                        <a href="{{ route('admin.events.edit', $event->id) }}" class="text-dark text-decoration-none" style="cursor: pointer;">
                                                            @if($start->format('Y-m-d') === $end->format('Y-m-d'))
                                                                {{ $start->format('M d, Y') }}
                                                            @else
                                                                {{ $start->format('M d') }} - {{ $end->format('M d, Y') }}
                                                            @endif
                                                        </a>
                                                        @if(!$loop->last)<br>@endif
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($activity['second_sem']))
                                                    @foreach($activity['second_sem'] as $semEvent)
                                                        @php
                                                            $start = $semEvent['start'];
                                                            $end = $semEvent['end'];
                                                            $event = $semEvent['event'];
                                                        @endphp
                                                        <a href="{{ route('admin.events.edit', $event->id) }}" class="text-dark text-decoration-none" style="cursor: pointer;">
                                                            @if($start->format('Y-m-d') === $end->format('Y-m-d'))
                                                                {{ $start->format('M d, Y') }}
                                                            @else
                                                                {{ $start->format('M d') }} - {{ $end->format('M d, Y') }}
                                                            @endif
                                                        </a>
                                                        @if(!$loop->last)<br>@endif
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($activity['midyear']))
                                                    @foreach($activity['midyear'] as $semEvent)
                                                        @php
                                                            $start = $semEvent['start'];
                                                            $end = $semEvent['end'];
                                                            $event = $semEvent['event'];
                                                        @endphp
                                                        <a href="{{ route('admin.events.edit', $event->id) }}" class="text-dark text-decoration-none" style="cursor: pointer;">
                                                            @if($start->format('Y-m-d') === $end->format('Y-m-d'))
                                                                {{ $start->format('M d, Y') }}
                                                            @else
                                                                {{ $start->format('M d') }} - {{ $end->format('M d, Y') }}
                                                            @endif
                                                        </a>
                                                        @if(!$loop->last)<br>@endif
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                No events scheduled for this academic year.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Special Dates Section -->
                @php
                    $specialDates = $events->filter(function($e) use ($year) {
                        $eventYear = \Carbon\Carbon::parse($e->start_time)->year;
                        return ($eventYear == $year || $eventYear == $year + 1) && 
                               in_array(strtolower($e->description), ['national holidays', 'city holidays', 'barangay holiday']);
                    })->sortBy('start_time');
                @endphp
                
                @if($specialDates->isNotEmpty())
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><strong>List of Special Dates within AY {{ $year }}-{{ $year + 1 }}</strong></h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" style="font-size: 0.9rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60%;">Event Name</th>
                                            <th style="width: 40%;">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($specialDates as $event)
                                            <tr>
                                                <td class="fw-bold">
                                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="text-dark text-decoration-none" style="cursor: pointer;">
                                                        {{ $event->name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @php
                                                        $start = \Carbon\Carbon::parse($event->start_time);
                                                        $end = \Carbon\Carbon::parse($event->end_time);
                                                    @endphp
                                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="text-dark text-decoration-none" style="cursor: pointer;">
                                                        @if($start->format('Y-m-d') === $end->format('Y-m-d'))
                                                            {{ $start->format('M d, Y') }}
                                                        @else
                                                            {{ $start->format('M d') }} - {{ $end->format('M d, Y') }}
                                                        @endif
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
            <!-- End List View -->
        </main>
    </div>
</div>

<style>
    .card-header {
        font-weight: bold;
    }
    .table-bordered {
        border-collapse: separate;
        border-spacing: 0;
    }
    .table-bordered td {
        position: relative;
        border: 1px solid #dee2e6;
    }
    .calendar-cell.in-month:hover {
        background-color: #e7f3ff !important;
    }
    .text-danger {
        color: #dc3545 !important;
    }
    .date-marker {
        font-size: 0.7rem;
    }
    .view-container {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    .table-warning {
        background-color: #fff3cd !important;
    }
    #listViewContainer table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
    }
    #listViewContainer table tbody td {
        vertical-align: middle;
    }
    /* Make event links more visible */
    a[href*="admin.events.edit"] {
        transition: all 0.2s ease;
    }
    a[href*="admin.events.edit"]:hover {
        text-decoration: underline !important;
        opacity: 0.8;
    }
    a[href*="admin.events.edit"].text-danger:hover {
        color: #dc3545 !important;
        font-weight: bold;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarView = document.getElementById('calendarView');
    const listView = document.getElementById('listView');
    const calendarContainer = document.getElementById('calendarViewContainer');
    const listContainer = document.getElementById('listViewContainer');
    
    // Get saved view preference from localStorage
    const savedView = localStorage.getItem('calendarViewPreference');
    if (savedView === 'list') {
        calendarView.checked = false;
        listView.checked = true;
        calendarContainer.style.display = 'none';
        listContainer.style.display = 'block';
    }
    
    calendarView.addEventListener('change', function() {
        if (this.checked) {
            calendarContainer.style.display = 'block';
            listContainer.style.display = 'none';
            localStorage.setItem('calendarViewPreference', 'calendar');
        }
    });
    
    listView.addEventListener('change', function() {
        if (this.checked) {
            calendarContainer.style.display = 'none';
            listContainer.style.display = 'block';
            localStorage.setItem('calendarViewPreference', 'list');
        }
    });
    
    // Make event links hoverable
    document.querySelectorAll('a[href*="admin.events.edit"]').forEach(function(link) {
        link.addEventListener('mouseenter', function() {
            this.style.textDecoration = 'underline';
        });
        link.addEventListener('mouseleave', function() {
            this.style.textDecoration = 'none';
        });
    });
});
</script>
@endsection
