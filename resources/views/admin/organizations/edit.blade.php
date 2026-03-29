@extends('layouts.app')

@section('title', 'Edit Organization')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main id="adminMain" class="col-md-10">
            <div class="admin-back-btn-wrap">
                <a href="{{ route('admin.organizations.index') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
            </div>
            
            <h2 class="mb-3">Edit Organization</h2>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.organizations.update', $organization) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Organization Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $organization->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="acronym" class="form-label">Acronym</label>
                                <input type="text" class="form-control @error('acronym') is-invalid @enderror" 
                                       id="acronym" name="acronym" value="{{ old('acronym', $organization->acronym) }}">
                                @error('acronym')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $organization->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="official_email" class="form-label">Official Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('official_email') is-invalid @enderror" 
                                       id="official_email" name="official_email" value="{{ old('official_email', $organization->official_email) }}" required>
                                @error('official_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="mailing_address" class="form-label">Mailing Address</label>
                            <textarea class="form-control @error('mailing_address') is-invalid @enderror" 
                                      id="mailing_address" name="mailing_address" rows="3">{{ old('mailing_address', $organization->mailing_address) }}</textarea>
                            @error('mailing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_established" class="form-label">Date Established</label>
                                <input type="date" class="form-control @error('date_established') is-invalid @enderror" 
                                       id="date_established" name="date_established" value="{{ old('date_established', $organization->date_established ? $organization->date_established->format('Y-m-d') : '') }}">
                                @error('date_established')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Special Organization</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_special" id="is_special" value="1" {{ old('is_special', $organization->is_special) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_special">
                                        Mark as special organization
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Organization</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

