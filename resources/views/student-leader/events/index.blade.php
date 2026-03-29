@extends('layouts.app')

@section('title', 'Assistant Events')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>Events</h2>
      {{-- List events for student leaders --}}
      {{-- @foreach($events as $event) ... @endforeach --}}
    </main>
  </div>
</div>
@endsection
