@extends('layouts.app')

@section('title', 'Event Requirements')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>Required Files for Event</h2>
      {{-- List required files for event creation --}}
      {{-- @foreach($requirements as $req) ... @endforeach --}}
    </main>
  </div>
</div>
@endsection
