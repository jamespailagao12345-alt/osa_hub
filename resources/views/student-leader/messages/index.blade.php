@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('assistant.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      <h2>Messages</h2>
      {{-- Messaging UI for student leaders and assigned staff --}}
    </main>
  </div>
</div>
@endsection
