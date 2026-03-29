@extends('layouts.app')

@section('title', 'Submit Event Feedback')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="admin-back-btn-wrap mb-3">
                <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Submit Feedback for Event</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>{{ $event->name }}</h5>
                        <p class="text-muted mb-2">
                            <strong>Date:</strong> 
                            @if($event->start_time)
                                {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y') }}
                            @else
                                N/A
                            @endif
                        </p>
                        <p class="text-muted mb-2">
                            <strong>Location:</strong> {{ $event->location ?? 'N/A' }}
                        </p>
                        @if($event->points)
                            <p class="text-success mb-0">
                                <strong>Points Available:</strong> {{ $event->points }} points
                            </p>
                        @endif
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.events.feedback.store', $event->id) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating (Optional)</label>
                            <select class="form-control" id="rating" name="rating">
                                <option value="">Select Rating</option>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very Good</option>
                                <option value="3">3 - Good</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="feedback_text" class="form-label">Feedback <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="feedback_text" name="feedback_text" rows="6" required minlength="10" maxlength="2000" placeholder="Please share your thoughts about this event...">{{ old('feedback_text') }}</textarea>
                            <small class="form-text text-muted">Minimum 10 characters, maximum 2000 characters.</small>
                            @error('feedback_text')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Feedback</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

