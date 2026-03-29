@extends('layouts.app')

@section('title', 'Participants History')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>Participants History</h2>
      {{-- List event participants filtered by department/courses --}}
      {{-- @foreach($participants as $participant) ... @endforeach --}}
    </main>
  </div>
</div>
@endsection
