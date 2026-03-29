@extends('layouts.app')

@section('title', 'Event Calendar')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>Event Calendar</h2>
      {{-- Read-only calendar view for student leaders --}}
      {{-- @foreach($calendarEvents as $event) ... @endforeach --}}
    </main>
  </div>
</div>
@endsection
