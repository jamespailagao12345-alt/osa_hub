@extends('layouts.app')

@section('title', 'Event Files')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>Event Files</h2>
      {{-- List/download/upload files for event --}}
      {{-- @foreach($files as $file) ... @endforeach --}}
    </main>
  </div>
</div>
@endsection
