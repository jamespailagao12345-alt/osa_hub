@extends('layouts.app')

@section('title', 'Staff Community')

@section('content')
<div class="container-fluid">
  <div class="row">
    @if(request()->routeIs('admin.*'))
      @include('admin.partials.sidebar')
    @else
      @include('staff.partials.sidebar')
    @endif

    <style>
      /* Match admin/show-staff color scheme */
      .card-header {
        background-color: midnightblue;
        color: #fff;
      }
      /* Ensure the community title wraps as a full-width block, not flex */
      .community-title { display: block !important; }
      .community-title h3 { margin: 0; }
      .community-title span {
        background-color: midnightblue; /* midnightblue background for main header */
        color: white; /* white text */
        display: block; /* full-width box */
        width: 100%;
        box-sizing: border-box;
        padding: .5rem 1rem; /* align with card header/content padding */
        border: none; /* remove all borders */
        border-radius: 0; /* no rounding for underline style */
        margin-left: 0; /* align edges with card below */
      }
      .list-group-item {
        background-color: #5DFFBF;
        color: #000;
      }
    </style>

  <div @if(request()->routeIs('admin.*')) id="adminMain" class="col-md-10" @else class="col-md-9 col-lg-10" @endif>
      @if(request()->routeIs('admin.*'))
      <div class="admin-back-btn-wrap">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
      </div>
      @endif
      <div class="d-flex justify-content-between align-items-center mb-3 community-title">
        <h3 class="mb-0"><span>Staff Community</span></h3>
      </div>

      <div class="card mb-4">
        <div class="card-header" style="background-color: midnightblue; color: white;">Post a Message</div>
        <div class="card-body">
          <form method="POST" action="{{ route('staff.community.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-end">
              <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" required>
                  <option value="announcement">Announcement</option>
                  <option value="inquiry">Inquiry</option>
                </select>
              </div>
              <div class="col-md-7">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="2" placeholder="Share an update or ask a question..." required></textarea>
              </div>
              <div class="col-md-12">
                <label class="form-label">Attachments</label>
                <input type="file" name="attachments[]" class="form-control" multiple />
              </div>
              <div class="col-md-12">
                <label class="form-label">Mentions (optional)</label>
                <input type="text" class="form-control" name="mentions_csv" placeholder="Enter user IDs separated by commas (simple stub)">
                <small class="text-muted">Note: For now, enter user IDs separated by commas. We can upgrade this to a searchable multi-select later.</small>
              </div>
              <div class="col-md-2 mt-2">
                <button type="submit" class="btn btn-primary w-100">Post</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header" style="background-color: #ffc107; color: white;">Latest Messages</div>
        <div class="card-body">
          @if($messages->isEmpty())
            <p class="text-muted">No messages yet.</p>
          @else
            <div class="list-group">
              @foreach($messages as $msg)
                <div class="list-group-item">
                  <div class="d-flex justify-content-between">
                    <div>
                      <span class="badge {{ $msg->type === 'announcement' ? 'bg-success' : 'bg-info' }} me-2 text-uppercase">{{ $msg->type }}</span>
                      <strong>{{ $msg->user->first_name }} {{ $msg->user->last_name }}</strong>
                      <small class="text-muted"> • {{ $msg->created_at->diffForHumans() }}</small>
                    </div>
                  </div>
                  <div class="mt-2">{{ $msg->content }}</div>
                  @if($msg->attachments->isNotEmpty())
                    <div class="mt-2">
                      <strong>Attachments:</strong>
                      <ul class="mb-0">
                        @foreach($msg->attachments as $att)
                          <li><a href="{{ Storage::disk('public')->url($att->path) }}" target="_blank">{{ $att->original_name }}</a></li>
                        @endforeach
                      </ul>
                    </div>
                  @endif
                  @if($msg->replies->isNotEmpty())
                    <div class="mt-3 ms-4">
                      @foreach($msg->replies as $rep)
                        <div class="border-start ps-3 mb-2">
                          <small class="text-muted">Reply by {{ $rep->user->first_name }} {{ $rep->user->last_name }} • {{ $rep->created_at->diffForHumans() }}</small>
                          <div>{{ $rep->content }}</div>
                        </div>
                      @endforeach
                    </div>
                  @endif
                  <div class="mt-3">
                    <form method="POST" action="{{ route('staff.community.store') }}" enctype="multipart/form-data">
                      @csrf
                      <input type="hidden" name="type" value="inquiry" />
                      <input type="hidden" name="parent_id" value="{{ $msg->id }}" />
                      <div class="row g-2 align-items-end">
                        <div class="col-md-9">
                          <input type="text" name="content" class="form-control" placeholder="Reply..." required />
                        </div>
                        <div class="col-md-3">
                          <input type="file" name="attachments[]" class="form-control" multiple />
                        </div>
                      </div>
                      <div class="mt-2 text-end">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Reply</button>
                      </div>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="mt-3">
              {{ $messages->links() }}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
