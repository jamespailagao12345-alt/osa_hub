@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main class="col-md-10 py-4">
        <div class="admin-back-btn-wrap">
            @if(request()->has('return_to'))
              <a href="{{ urldecode(request('return_to')) }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            @else
              <a href="{{ route('admin.events.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back to Events</a>
            @endif
        </div>
        <div class="py-3">
            <h1 class="h4 mb-4">
                <span class="badge bg-warning text-dark me-2">Pending</span>
                Pending Events
                <span class="badge bg-secondary">{{ $events->total() }}</span>
            </h1>
            <p class="text-muted small mb-3">Events created by staff but still need approval</p>

            <!-- Search and Filter Form -->
            <form method="GET" action="{{ route('admin.events.pending') }}" class="mb-4">
                @if(request()->has('return_to'))
                  <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                @endif
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Events</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or description...">
                    </div>
                    <div class="col-md-3">
                        <label for="description" class="form-label">Filter by Description</label>
                        <select class="form-control" id="description" name="description">
                            <option value="">All Descriptions</option>
                            @foreach($descriptions ?? [] as $desc)
                                <option value="{{ $desc }}" {{ request('description') == $desc ? 'selected' : '' }}>{{ $desc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="organization_id" class="form-label">Filter by Organization/Coordinator</label>
                        <select class="form-control" id="organization_id" name="organization_id">
                            <option value="">All Organizations</option>
                            @foreach($organizations ?? [] as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
                @if(request()->hasAny(['search', 'description', 'organization_id']))
                    @if(request()->has('return_to'))
                      <a href="{{ route('admin.events.pending', ['return_to' => request('return_to')]) }}" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                    @else
                      <a href="{{ route('admin.events.pending') }}" class="btn btn-outline-secondary btn-sm">Clear Filters</a>
                    @endif
                @endif
            </form>

            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Description</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Location</th>
                            <th>Coordinator</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                        <tr>
                            <td><strong>{{ $event->name }}</strong></td>
                            <td>{{ $event->description ?? 'N/A' }}</td>
                            <td>
                                @if($event->start_time)
                                    {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y h:i A') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($event->end_time)
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('M d, Y h:i A') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $event->location ?? 'N/A' }}</td>
                            <td>{{ $event->organization->name ?? ($event->coordinator_name ?? 'N/A') }}</td>
                            <td>{{ $event->creator->first_name ?? '' }} {{ $event->creator->last_name ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-primary">View</a>
                                    <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this event?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#declineModal{{ $event->id }}">
                                        Decline
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No pending events at the moment.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $events->links() }}
            </div>
            
            <!-- Decline Event Modals -->
            @foreach($events as $event)
            <div class="modal fade" id="declineModal{{ $event->id }}" tabindex="-1" aria-labelledby="declineModalLabel{{ $event->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="declineModalLabel{{ $event->id }}">Decline Event: {{ $event->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.events.decline', $event->id) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> Once declined, this event cannot be edited or updated. Please provide a reason for declining.
                                </div>
                                <div class="mb-3">
                                    <label for="reason{{ $event->id }}" class="form-label">Reason for Decline <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reason{{ $event->id }}" name="reason" rows="4" placeholder="Enter the reason why this event is being declined..." required minlength="5" maxlength="1000">{{ old('reason') }}</textarea>
                                    <small class="form-text text-muted">Minimum 5 characters, maximum 1000 characters. This reason will be sent to the organization via email and in-app notification.</small>
                                    @error('reason')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Decline Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
            
            <style>
                /* Completely remove pagination arrow buttons - comprehensive approach */
                .pagination .page-item:first-child,
                .pagination .page-item:last-child,
                .pagination .page-item:nth-child(1),
                .pagination .page-item:nth-last-child(1),
                .pagination li:first-child,
                .pagination li:last-child,
                .pagination li:nth-child(1),
                .pagination li:nth-last-child(1),
                .pagination a[rel="prev"],
                .pagination a[rel="next"],
                .pagination .page-link[rel="prev"],
                .pagination .page-link[rel="next"],
                ul.pagination > li:first-child,
                ul.pagination > li:last-child,
                ul.pagination > li:nth-child(1),
                ul.pagination > li:nth-last-child(1),
                .pagination > .page-item:first-child,
                .pagination > .page-item:last-child,
                .pagination > .page-item:nth-child(1),
                .pagination > .page-item:nth-last-child(1),
                .pagination > li:first-child,
                .pagination > li:last-child,
                .pagination > li:nth-child(1),
                .pagination > li:nth-last-child(1),
                .pagination .disabled:first-child,
                .pagination .disabled:last-child,
                .pagination .disabled:nth-child(1),
                .pagination .disabled:nth-last-child(1) {
                    display: none !important;
                    visibility: hidden !important;
                    opacity: 0 !important;
                    width: 0 !important;
                    height: 0 !important;
                    min-width: 0 !important;
                    min-height: 0 !important;
                    max-width: 0 !important;
                    max-height: 0 !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    font-size: 0 !important;
                    line-height: 0 !important;
                    overflow: hidden !important;
                    position: absolute !important;
                    left: -9999px !important;
                    clip: rect(0, 0, 0, 0) !important;
                    pointer-events: none !important;
                }
            </style>
            <script>
                // Aggressively remove pagination arrow buttons from DOM
                (function() {
                    function removePaginationArrows() {
                        // Find all pagination containers
                        const pagination = document.querySelectorAll('.pagination, ul.pagination');
                        pagination.forEach(function(pag) {
                            if (!pag || !pag.parentNode) return;
                            
                            // Get all list items - work backwards to avoid index issues
                            const items = Array.from(pag.querySelectorAll('.page-item, li'));
                            
                            items.forEach(function(item) {
                                if (!item || !item.parentNode) return;
                                
                                const link = item.querySelector('.page-link, a');
                                if (link) {
                                    const text = link.textContent.trim();
                                    const innerHTML = link.innerHTML.trim();
                                    const rel = link.getAttribute('rel');
                                    const href = link.getAttribute('href') || '';
                                    
                                    // Check for various arrow indicators
                                    const isArrow = 
                                        text === 'Previous' || 
                                        text === 'Next' || 
                                        text === '«' || 
                                        text === '»' ||
                                        text === '‹' || 
                                        text === '›' ||
                                        text === '&laquo;' || 
                                        text === '&raquo;' ||
                                        innerHTML.includes('&laquo;') ||
                                        innerHTML.includes('&raquo;') ||
                                        innerHTML.includes('«') ||
                                        innerHTML.includes('»') ||
                                        innerHTML.includes('Previous') ||
                                        innerHTML.includes('Next') ||
                                        innerHTML.includes('chevron-left') ||
                                        innerHTML.includes('chevron-right') ||
                                        innerHTML.includes('arrow-left') ||
                                        innerHTML.includes('arrow-right') ||
                                        rel === 'prev' || 
                                        rel === 'next' ||
                                        (href.includes('page=') && (text === '' || text.length <= 2));
                                    
                                    if (isArrow) {
                                        // Force remove from DOM
                                        try {
                                            item.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important; width: 0 !important; height: 0 !important; padding: 0 !important; margin: 0 !important; font-size: 0 !important;';
                                            if (item.parentNode) {
                                                item.remove();
                                            }
                                        } catch(e) {
                                            // If remove fails, at least hide it
                                            item.style.display = 'none';
                                        }
                                    }
                                }
                            });
                            
                            // Also check and remove first/last children directly
                            const children = Array.from(pag.children);
                            if (children.length > 0) {
                                // Remove first child if it looks like Previous
                                const firstChild = children[0];
                                if (firstChild) {
                                    const firstLink = firstChild.querySelector('.page-link, a');
                                    if (firstLink) {
                                        const firstText = firstLink.textContent.trim();
                                        const firstHTML = firstLink.innerHTML.trim();
                                        const firstRel = firstLink.getAttribute('rel');
                                        if (firstText === 'Previous' || firstText === '«' || firstText === '&laquo;' || firstText === '‹' || 
                                            firstHTML.includes('Previous') || firstHTML.includes('«') || firstHTML.includes('&laquo;') ||
                                            firstRel === 'prev') {
                                            try {
                                                firstChild.style.cssText = 'display: none !important;';
                                                if (firstChild.parentNode) {
                                                    firstChild.remove();
                                                }
                                            } catch(e) {
                                                firstChild.style.display = 'none';
                                            }
                                        }
                                    }
                                }
                                
                                // Remove last child if it looks like Next
                                const lastChild = children[children.length - 1];
                                if (lastChild && lastChild !== firstChild) {
                                    const lastLink = lastChild.querySelector('.page-link, a');
                                    if (lastLink) {
                                        const lastText = lastLink.textContent.trim();
                                        const lastHTML = lastLink.innerHTML.trim();
                                        const lastRel = lastLink.getAttribute('rel');
                                        if (lastText === 'Next' || lastText === '»' || lastText === '&raquo;' || lastText === '›' ||
                                            lastHTML.includes('Next') || lastHTML.includes('»') || lastHTML.includes('&raquo;') ||
                                            lastRel === 'next') {
                                            try {
                                                lastChild.style.cssText = 'display: none !important;';
                                                if (lastChild.parentNode) {
                                                    lastChild.remove();
                                                }
                                            } catch(e) {
                                                lastChild.style.display = 'none';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                    
                    // Run immediately
                    removePaginationArrows();
                    
                    // Run when DOM is ready
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', removePaginationArrows);
                    } else {
                        removePaginationArrows();
                    }
                    
                    // Run multiple times aggressively
                    setTimeout(removePaginationArrows, 10);
                    setTimeout(removePaginationArrows, 50);
                    setTimeout(removePaginationArrows, 100);
                    setTimeout(removePaginationArrows, 200);
                    setTimeout(removePaginationArrows, 300);
                    setTimeout(removePaginationArrows, 500);
                    setTimeout(removePaginationArrows, 1000);
                    setTimeout(removePaginationArrows, 2000);
                    
                    // Use MutationObserver to catch any future changes
                    const observer = new MutationObserver(function(mutations) {
                        removePaginationArrows();
                    });
                    observer.observe(document.body, {
                        childList: true,
                        subtree: true,
                        attributes: false,
                        characterData: false
                    });
                    
                    // Also observe on window load
                    window.addEventListener('load', removePaginationArrows);
                    
                    // Continuous monitoring every 500ms for first 5 seconds
                    let monitorCount = 0;
                    const monitorInterval = setInterval(function() {
                        removePaginationArrows();
                        monitorCount++;
                        if (monitorCount >= 10) {
                            clearInterval(monitorInterval);
                        }
                    }, 500);
                })();
            </script>
            
            <!-- Admin Created Events Section -->
            <div class="mt-5">
                <h3 class="h6 mb-3">
                    <span class="badge bg-primary me-2">Admin Created</span>
                    Admin Created Events
                    <span class="badge bg-secondary">{{ $adminEvents->total() ?? 0 }}</span>
                </h3>
                <p class="text-muted small mb-3">Events created by administrators</p>
                
                <div class="bg-white shadow rounded-lg overflow-x-auto">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Description</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Location</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adminEvents ?? [] as $event)
                            <tr>
                                <td><strong>{{ $event->name }}</strong></td>
                                <td>{{ $event->description ?? 'N/A' }}</td>
                                <td>
                                    @if($event->start_time)
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($event->end_time)
                                        {{ \Carbon\Carbon::parse($event->end_time)->format('M d, Y h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $event->location ?? 'N/A' }}</td>
                                <td>{{ $event->creator->first_name ?? '' }} {{ $event->creator->last_name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-sm btn-warning me-1">Edit</a>
                                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-primary">View Details</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No admin-created pending events at the moment.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if(isset($adminEvents) && $adminEvents->hasPages())
                <div class="mt-3">
                    {{ $adminEvents->links() }}
                </div>
                @endif
            </div>
        </div>
        </main>
    </div>
</div>
@endsection
