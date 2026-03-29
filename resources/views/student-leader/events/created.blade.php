@extends('layouts.app')

@section('title', 'Created Events')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>My Created Events</h2>
      {{-- List events created by student leaders --}}
      {{-- @foreach($createdEvents as $event) ... @endforeach --}}
    </main>
  </div>
</div>
@endsection
