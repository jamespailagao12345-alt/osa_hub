@extends('layouts.app')

@section('title', 'My Organizations')

@section('content')
<div class="container-fluid">
  
  <div class="row">
    @include('staff.partials.sidebar')
    <main id="staffMain" class="col-md-10">
      @php
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        $isStaff = $currentUser && (int) $currentUser->role === 2;
        
        // Get designation from multiple sources
        $userDesignation = null;
        if ($currentUser) {
          $userDesignation = $currentUser->designation 
            ?? optional($currentUser->staffProfile)->designation;
          
          // If still not found, check Staff table by email (case-insensitive)
          if (!$userDesignation) {
            $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
            $userDesignation = $staffRecord ? $staffRecord->designation : null;
          }
        }
      @endphp
      <div class="admin-back-btn-wrap">
        @if($isAdmin)
          <a href="{{ route('admin.staff.dashboard') }}" class="btn btn-secondary">Back</a>
        @elseif($isStaff && $userDesignation)
          <a href="{{ route('admin.staff.dashboard.designation', ['designation' => $userDesignation]) }}" class="btn btn-secondary">Back</a>
        @else
          <a href="{{ route('admin.staff.dashboard') }}" class="btn btn-secondary">Back</a>
        @endif
      </div>
      <h2 class="mb-3">My Organizations</h2>
      
      @if(!isset($organizationsWithStats) || $organizationsWithStats->isEmpty())
        <div class="alert alert-info">
          <p>You are not assigned to any organizations yet.</p>
        </div>
      @else
        <div class="row">
          @foreach($organizationsWithStats as $orgData)
            @php
              $organization = $orgData['organization'];
              $totalMembers = $orgData['total_members'];
              $maleCount = $orgData['male_count'];
              $femaleCount = $orgData['female_count'];
              $otherCount = $orgData['other_count'];
              $yearLevelCounts = $orgData['year_level_counts'];
            @endphp
            <div class="col-12 mb-4">
              <div class="card">
                <div class="card-header" style="background-color: midnightblue; color: white;">
                  <h5 class="mb-0">
                    <a href="{{ route('admin.organizations.profile', $organization->id) }}" style="color: white; text-decoration: none; cursor: pointer;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                      {{ $organization->name }}
                    </a>
                  </h5>
                  @if($organization->department)
                    <small>{{ $organization->department->name }} - Academic Organization</small>
                  @else
                    <small>Non-Academic Organization</small>
                  @endif
                </div>
                <div class="card-body">
                  <!-- Organizational Profile Section -->
                  <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3" style="background-color: #ffc107; color: white; padding: .5rem 1rem; font-weight: bold; margin: 0 -1rem 1rem -1rem;">ORGANIZATIONAL PROFILE</h6>
                    <div class="row g-2 mb-2">
                      <div class="col-5">
                        <strong>Acronym:</strong>
                      </div>
                      <div class="col-7">
                        {{ $organization->acronym ?? 'N/A' }}
                      </div>
                    </div>
                    <div class="row g-2 mb-2">
                      <div class="col-5">
                        <strong>Mailing Address:</strong>
                      </div>
                      <div class="col-7">
                        {{ $organization->mailing_address ?? 'N/A' }}
                      </div>
                    </div>
                    <div class="row g-2 mb-2">
                      <div class="col-5">
                        <strong>Org. Email Address:</strong>
                      </div>
                      <div class="col-7">
                        {{ $organization->official_email ?? 'N/A' }}
                      </div>
                    </div>
                    <div class="row g-2 mb-3">
                      <div class="col-5">
                        <strong>Date Established:</strong>
                      </div>
                      <div class="col-7">
                        @if($organization->date_established)
                          {{ \Carbon\Carbon::parse($organization->date_established)->format('F d, Y') }}
                        @else
                          N/A
                        @endif
                      </div>
                    </div>
                  </div>
                  
                  <!-- Edit Organization Modal -->
                  <div class="modal fade" id="editOrganizationModal{{ $organization->id }}" tabindex="-1" aria-labelledby="editOrganizationModalLabel{{ $organization->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                          <h5 class="modal-title" id="editOrganizationModalLabel{{ $organization->id }}">Edit Organization Details - {{ $organization->name }}</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('staff.organizations.update', $organization->id) }}">
                          @csrf
                          @method('PUT')
                          <div class="modal-body">
                            <div class="mb-3">
                              <label for="acronym{{ $organization->id }}" class="form-label">Acronym</label>
                              <input type="text" class="form-control" id="acronym{{ $organization->id }}" name="acronym" value="{{ old('acronym', $organization->acronym) }}" placeholder="e.g., SCIT">
                            </div>
                            <div class="mb-3">
                              <label for="mailing_address{{ $organization->id }}" class="form-label">Mailing Address</label>
                              <textarea class="form-control" id="mailing_address{{ $organization->id }}" name="mailing_address" rows="2" placeholder="Enter mailing address">{{ old('mailing_address', $organization->mailing_address) }}</textarea>
                            </div>
                            <div class="mb-3">
                              <label for="official_email{{ $organization->id }}" class="form-label">Org. Email Address</label>
                              <input type="email" class="form-control" id="official_email{{ $organization->id }}" name="official_email" value="{{ old('official_email', $organization->official_email) }}" placeholder="organization@ustp.edu.ph">
                              <small class="form-text text-muted">This email will be used to send notifications about events.</small>
                            </div>
                            <div class="mb-3">
                              <label for="date_established{{ $organization->id }}" class="form-label">Date Established</label>
                              <input type="date" class="form-control" id="date_established{{ $organization->id }}" name="date_established" value="{{ old('date_established', $organization->date_established ? \Carbon\Carbon::parse($organization->date_established)->format('Y-m-d') : '') }}">
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">Save Changes</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                  <!-- Membership Distribution Table -->
                  <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3" style="background-color: #ffc107; color: white; padding: .5rem 1rem; font-weight: bold; margin: 0 -1rem 1rem -1rem;">MEMBERSHIP DISTRIBUTION</h6>
                    <div class="table-responsive">
                      <table class="table table-bordered table-sm" style="font-size: 0.875rem;">
                        <thead style="background-color: #f0f0f0;">
                          <tr class="text-center">
                            <th rowspan="2" style="vertical-align: middle; width: 80px;">YEAR LEVEL</th>
                            <th colspan="3" style="border-bottom: 1px solid #ddd;">SEX</th>
                            <th rowspan="2" style="vertical-align: middle;">TOTAL</th>
                          </tr>
                          <tr style="border-top: none;">
                            <th>Male</th>
                            <th>Female</th>
                            <th>Other</th>
                          </tr>
                        </thead>
                        <tbody>
                          @for($year = 1; $year <= 5; $year++)
                            <tr class="text-center">
                              <td><strong>{{ $year }}{{ $year == 1 ? 'st' : ($year == 2 ? 'nd' : ($year == 3 ? 'rd' : 'th')) }}</strong></td>
                              <td>{{ $yearLevelCounts[$year]['male'] ?? 0 }}</td>
                              <td>{{ $yearLevelCounts[$year]['female'] ?? 0 }}</td>
                              <td>{{ $yearLevelCounts[$year]['other'] ?? 0 }}</td>
                              <td><strong>{{ $yearLevelCounts[$year]['total'] ?? 0 }}</strong></td>
                            </tr>
                          @endfor
                          <tr class="text-center" style="background-color: #f0f0f0; font-weight: bold;">
                            <td><strong>TOTAL</strong></td>
                            <td>{{ $maleCount }}</td>
                            <td>{{ $femaleCount }}</td>
                            <td>{{ $otherCount }}</td>
                            <td><strong>{{ $totalMembers }}</strong></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="d-flex flex-column gap-2 border-top pt-3">
                    <a href="{{ route('staff.organizations.assistants', $organization->id) }}" class="btn btn-primary">
                      <i class="bi bi-people"></i> My Student Leaders
                    </a>
                    <a href="{{ route('staff.student-leaders.create', ['organization_id' => $organization->id]) }}" class="btn btn-success">
                      <i class="bi bi-person-plus"></i> Add Assistant
                    </a>
                    <a href="{{ route('admin.staff.dashboard.StudentOrgModerator.create-event', ['organization_id' => $organization->id]) }}" class="btn btn-warning">
                      <i class="bi bi-calendar-event"></i> Create Event
                    </a>
                    <a href="{{ route('staff.organization-files.index', $organization->id) }}" class="btn btn-info">
                      <i class="bi bi-folder"></i> Organization Files
                    </a>
                  </div>
                  
                  <!-- Events Created by Organization -->
                  <div class="mt-4 border-top pt-3">
                    <h6 class="border-bottom pb-2 mb-3" style="background-color: #28a745; color: white; padding: .5rem 1rem; font-weight: bold; margin: 0 -1rem 1rem -1rem;">EVENTS CREATED BY ORGANIZATION</h6>
                    @php
                      $orgEvents = $orgData['organization_events'] ?? collect();
                    @endphp
                    @if($orgEvents->isEmpty())
                      <p class="text-muted">No events created yet.</p>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                          <thead style="background-color: #f0f0f0;">
                            <tr>
                              <th>Event Name</th>
                              <th>Date</th>
                              <th>Status</th>
                              <th>Creator</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($orgEvents as $event)
                              <tr>
                                <td>{{ $event->name }}</td>
                                <td>
                                  @if($event->event_date)
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                                  @else
                                    N/A
                                  @endif
                                </td>
                                <td>
                                  @if($event->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                  @elseif($event->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                  @elseif($event->status === 'declined')
                                    <span class="badge bg-danger">Declined</span>
                                  @else
                                    <span class="badge bg-secondary">{{ ucfirst($event->status) }}</span>
                                  @endif
                                </td>
                                <td>
                                  @if($event->creator)
                                    {{ $event->creator->first_name }} {{ $event->creator->last_name }}
                                  @else
                                    N/A
                                  @endif
                                </td>
                                <td>
                                  <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  </div>
                  
                  <!-- Pending Events -->
                  <div class="mt-4 border-top pt-3">
                    <h6 class="border-bottom pb-2 mb-3" style="background-color: #ffc107; color: white; padding: .5rem 1rem; font-weight: bold; margin: 0 -1rem 1rem -1rem;">PENDING EVENTS</h6>
                    @php
                      $pendingEvents = $orgData['pending_events'] ?? collect();
                    @endphp
                    @if($pendingEvents->isEmpty())
                      <p class="text-muted">No pending events.</p>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                          <thead style="background-color: #f0f0f0;">
                            <tr>
                              <th>Event Name</th>
                              <th>Date</th>
                              <th>Creator</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($pendingEvents as $event)
                              <tr>
                                <td>{{ $event->name }}</td>
                                <td>
                                  @if($event->event_date)
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                                  @else
                                    N/A
                                  @endif
                                </td>
                                <td>
                                  @if($event->creator)
                                    {{ $event->creator->first_name }} {{ $event->creator->last_name }}
                                  @else
                                    N/A
                                  @endif
                                </td>
                                <td>
                                  <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  </div>
                  
                  <!-- Events History -->
                  <div class="mt-4 border-top pt-3">
                    <h6 class="border-bottom pb-2 mb-3" style="background-color: #17a2b8; color: white; padding: .5rem 1rem; font-weight: bold; margin: 0 -1rem 1rem -1rem;">EVENTS HISTORY</h6>
                    @php
                      $eventsHistory = $orgData['events_history'] ?? collect();
                    @endphp
                    @if($eventsHistory->isEmpty())
                      <p class="text-muted">No events history available.</p>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                          <thead style="background-color: #f0f0f0;">
                            <tr>
                              <th>Event Name</th>
                              <th>Date</th>
                              <th>Status</th>
                              <th>Creator</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($eventsHistory as $event)
                              <tr>
                                <td>{{ $event->name }}</td>
                                <td>
                                  @if($event->event_date)
                                    {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}
                                  @else
                                    N/A
                                  @endif
                                </td>
                                <td>
                                  @if($event->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                  @elseif($event->status === 'declined')
                                    <span class="badge bg-danger">Declined</span>
                                    @if($event->decline_reason)
                                      <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($event->decline_reason, 50) }}</small>
                                    @endif
                                  @else
                                    <span class="badge bg-secondary">{{ ucfirst($event->status) }}</span>
                                  @endif
                                </td>
                                <td>
                                  @if($event->creator)
                                    {{ $event->creator->first_name }} {{ $event->creator->last_name }}
                                  @else
                                    N/A
                                  @endif
                                </td>
                                <td>
                                  <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  </div>
                  
                  <!-- QR Code Scanner Section -->
                  <div class="mt-4 border-top pt-3">
                    <h6 class="border-bottom pb-2 mb-3" style="background-color: #ffc107; color: white; padding: .5rem 1rem; font-weight: bold; margin: 0 -1rem 1rem -1rem;">QR CODE SCANNER - EVENT PARTICIPATION</h6>
                    <div class="row">
                      <div class="col-md-12 mb-3">
                        <label for="qrEventSelect{{ $organization->id }}" class="form-label">Select Event:</label>
                        <select id="qrEventSelect{{ $organization->id }}" class="form-control">
                          <option value="">-- Select an event --</option>
                          @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->title ?? $event->name }} - {{ \Carbon\Carbon::parse($event->start_time ?? $event->event_date)->format('M d, Y') }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-12 mb-3">
                        <label for="qrScannerInput{{ $organization->id }}" class="form-label" style="cursor: pointer;" id="qrLabel{{ $organization->id }}">
                          QR Code Data: <small class="text-muted">(click here or Upload button to select file)</small>
                        </label>
                        <div class="input-group">
                          <input type="text" id="qrScannerInput{{ $organization->id }}" class="form-control" placeholder="Scan QR code, paste QR data, or click 'Upload' to select file" autocomplete="off">
                          <input type="file" id="qrFileUpload{{ $organization->id }}" class="d-none" accept="image/*,.txt,.json" />
                          <button type="button" id="uploadQrBtn{{ $organization->id }}" class="btn btn-info" title="Upload QR code file from device">
                            <i class="bi bi-upload"></i> Upload
                          </button>
                          <button type="button" id="startCameraBtn{{ $organization->id }}" class="btn btn-primary">
                            <i class="bi bi-camera"></i> Start Camera
                          </button>
                          <button type="button" id="stopCameraBtn{{ $organization->id }}" class="btn btn-secondary" style="display: none;">
                            <i class="bi bi-camera-video-off"></i> Stop Camera
                          </button>
                        </div>
                        <small class="form-text text-muted">Click "Upload" to select a QR code image (PNG, JPG) or text file (.txt, .json) from your device</small>
                      </div>
                    </div>
                    
                    <!-- Camera Preview -->
                    <div id="cameraPreview{{ $organization->id }}" class="mb-3" style="display: none;">
                      <video id="qrVideo{{ $organization->id }}" width="100%" height="300" autoplay playsinline style="border: 2px solid midnightblue; border-radius: 4px;"></video>
                      <canvas id="qrCanvas{{ $organization->id }}" style="display: none;"></canvas>
                    </div>
                    
                    <!-- Scan Results -->
                    <div id="scanResults{{ $organization->id }}" class="mt-3"></div>
                    
                    <!-- Recent Scans -->
                    <div class="mt-4">
                      <h6>Recent Scans</h6>
                      <div id="recentScans{{ $organization->id }}" class="list-group" style="max-height: 300px; overflow-y: auto;">
                        <p class="text-muted">No scans yet. Start scanning to see results here.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </main>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
  // QR Code Scanner functionality for each organization
  @foreach($organizationsWithStats as $orgData)
    @php
      $organization = $orgData['organization'];
    @endphp
    (function() {
      const orgId = {{ $organization->id }};
      let qrStream{{ $organization->id }} = null;
      let qrScanInterval{{ $organization->id }} = null;
      let isProcessing{{ $organization->id }} = false; // Flag to prevent multiple simultaneous scans
      const recentScans{{ $organization->id }} = [];
      
      const qrScannerInput = document.getElementById('qrScannerInput' + orgId);
      const qrEventSelect = document.getElementById('qrEventSelect' + orgId);
      const qrFileUpload = document.getElementById('qrFileUpload' + orgId);
      const uploadQrBtn = document.getElementById('uploadQrBtn' + orgId);
      const qrLabel = document.getElementById('qrLabel' + orgId);
      const startCameraBtn = document.getElementById('startCameraBtn' + orgId);
      const stopCameraBtn = document.getElementById('stopCameraBtn' + orgId);
      const cameraPreview = document.getElementById('cameraPreview' + orgId);
      const qrVideo = document.getElementById('qrVideo' + orgId);
      const qrCanvas = document.getElementById('qrCanvas' + orgId);
      const scanResults = document.getElementById('scanResults' + orgId);
      const recentScansDiv = document.getElementById('recentScans' + orgId);
      
      if (!qrScannerInput || !qrEventSelect) return;
      
      // Allow pasting into input - auto-process on paste
      qrScannerInput.addEventListener('paste', function() {
        setTimeout(function() {
          // After paste, process the value if there's content
          if (qrScannerInput.value && qrScannerInput.value.trim()) {
            processQRCode(qrScannerInput.value.trim());
          }
        }, 100);
      });
      
      // Upload QR file button
      if (uploadQrBtn && qrFileUpload) {
        uploadQrBtn.addEventListener('click', function() {
          qrFileUpload.click();
        });
      }
      
      // Allow clicking on label to upload file
      if (qrLabel && qrFileUpload) {
        qrLabel.addEventListener('click', function(e) {
          // Only trigger if clicking the label itself, not nested elements
          if (e.target === qrLabel || e.target.tagName === 'SMALL') {
            qrFileUpload.click();
          }
        });
      }
      
      // Handle file upload
      if (qrFileUpload) {
        qrFileUpload.addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (!file) return;
          
          // Check file type
          if (file.type.startsWith('image/')) {
            // Image file - decode QR code
            const reader = new FileReader();
            reader.onload = function(event) {
              const img = new Image();
              img.onload = function() {
                // Create a temporary canvas to process the image
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = img.width;
                tempCanvas.height = img.height;
                const ctx = tempCanvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                
                // Try to decode QR code from image
                const imageData = ctx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
                if (typeof jsQR !== 'undefined') {
                  const code = jsQR(imageData.data, imageData.width, imageData.height);
                  if (code && code.data) {
                    qrScannerInput.value = code.data;
                    processQRCode(code.data);
                  } else {
                    showMessage('No QR code found in the image. Please try a different image.', 'warning');
                  }
                } else {
                  showMessage('QR scanner library not loaded. Please refresh the page.', 'danger');
                }
              };
              img.onerror = function() {
                showMessage('Error loading image file.', 'danger');
              };
              img.src = event.target.result;
            };
            reader.onerror = function() {
              showMessage('Error reading file.', 'danger');
            };
            reader.readAsDataURL(file);
          } else if (file.type === 'text/plain' || file.name.endsWith('.txt') || file.name.endsWith('.json')) {
            // Text file - read contents
            const reader = new FileReader();
            reader.onload = function(event) {
              const content = event.target.result.trim();
              qrScannerInput.value = content;
              processQRCode(content);
            };
            reader.onerror = function() {
              showMessage('Error reading file.', 'danger');
            };
            reader.readAsText(file);
          } else {
            showMessage('Unsupported file type. Please upload an image or text file.', 'warning');
          }
          
          // Reset file input
          e.target.value = '';
        });
      }
      
      // Manual QR code input
      qrScannerInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          processQRCode(qrScannerInput.value);
        }
      });
      
      // Start camera
      if (startCameraBtn) {
        startCameraBtn.addEventListener('click', function() {
          startQRScanner();
        });
      }
      
      // Stop camera
      if (stopCameraBtn) {
        stopCameraBtn.addEventListener('click', function() {
          stopQRScanner();
        });
      }
      
      function startQRScanner() {
        // Check if jsQR is loaded
        if (typeof jsQR === 'undefined') {
          alert('QR scanner library not loaded. Please refresh the page.');
          console.error('jsQR library not found');
          return;
        }
        
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
          .then(function(stream) {
            qrStream{{ $organization->id }} = stream;
            qrVideo.srcObject = stream;
            cameraPreview.style.display = 'block';
            if (startCameraBtn) startCameraBtn.style.display = 'none';
            if (stopCameraBtn) stopCameraBtn.style.display = 'inline-block';
            
            // Wait for video to be ready and start playing
            qrVideo.onloadedmetadata = function() {
              qrVideo.play().catch(function(err) {
                console.error('Video play error:', err);
                showMessage('Unable to start video. Please check camera permissions.', 'warning');
              });
            };
            
            // Also try to play immediately
            qrVideo.play().catch(function(err) {
              console.error('Immediate video play error:', err);
            });
            
            // Start scanning with jsQR library after a short delay to ensure video is playing
            setTimeout(function() {
              qrScanInterval{{ $organization->id }} = setInterval(function() {
                if (isProcessing{{ $organization->id }}) return; // Skip if already processing
                
                if (qrVideo.readyState === qrVideo.HAVE_ENOUGH_DATA && 
                    qrVideo.videoWidth > 0 && 
                    qrVideo.videoHeight > 0 &&
                    !qrVideo.paused &&
                    !qrVideo.ended) {
                  qrCanvas.width = qrVideo.videoWidth;
                  qrCanvas.height = qrVideo.videoHeight;
                  const ctx = qrCanvas.getContext('2d');
                  ctx.drawImage(qrVideo, 0, 0, qrCanvas.width, qrCanvas.height);
                  const imageData = ctx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
                  
                  if (typeof jsQR !== 'undefined') {
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    if (code && code.data) {
                      isProcessing{{ $organization->id }} = true;
                      stopQRScanner(); // Stop scanning after successful scan
                      processQRCode(code.data);
                      qrScannerInput.value = code.data;
                    }
                  }
                }
              }, 250); // Reduced interval for faster scanning
            }, 500); // Wait 500ms for video to start playing
          })
          .catch(function(err) {
            alert('Camera access denied. Please allow camera access or use manual input.');
            console.error('Camera error:', err);
          });
      }
      
      function stopQRScanner() {
        isProcessing{{ $organization->id }} = false; // Reset processing flag
        if (qrStream{{ $organization->id }}) {
          qrStream{{ $organization->id }}.getTracks().forEach(track => track.stop());
          qrStream{{ $organization->id }} = null;
        }
        if (qrScanInterval{{ $organization->id }}) {
          clearInterval(qrScanInterval{{ $organization->id }});
          qrScanInterval{{ $organization->id }} = null;
        }
        cameraPreview.style.display = 'none';
        if (startCameraBtn) startCameraBtn.style.display = 'inline-block';
        if (stopCameraBtn) stopCameraBtn.style.display = 'none';
        qrVideo.srcObject = null;
      }
      
      function processQRCode(qrData) {
        if (!qrData || qrData.trim() === '') {
          isProcessing{{ $organization->id }} = false;
          showMessage('Please enter or scan a QR code.', 'warning');
          return;
        }
        
        const eventId = qrEventSelect.value;
        if (!eventId) {
          isProcessing{{ $organization->id }} = false;
          showMessage('Please select an event first.', 'warning');
          return;
        }
        
        scanResults.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Processing QR code...</div>';
        
        fetch('{{ route("staff.scan-qr") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            qr_data: qrData,
            event_id: eventId
          })
        })
        .then(response => response.json())
        .then(data => {
          isProcessing{{ $organization->id }} = false; // Reset flag after processing
          if (data.success) {
            showMessage(data.message, 'success');
            addRecentScan(data.student, data.event, new Date().toLocaleString());
            qrScannerInput.value = '';
            qrScannerInput.focus();
          } else {
            showMessage(data.message, 'danger');
          }
        })
        .catch(error => {
          isProcessing{{ $organization->id }} = false; // Reset flag on error
          console.error('QR scan error:', error);
          showMessage('An error occurred while processing the QR code.', 'danger');
        });
      }
      
      function showMessage(message, type) {
        scanResults.innerHTML = '<div class="alert alert-' + type + '">' + message + '</div>';
      }
      
      function addRecentScan(student, event, timestamp) {
        recentScans{{ $organization->id }}.unshift({ student, event, timestamp });
        if (recentScans{{ $organization->id }}.length > 10) recentScans{{ $organization->id }}.pop();
        
        if (recentScans{{ $organization->id }}.length === 0) {
          recentScansDiv.innerHTML = '<p class="text-muted">No scans yet. Start scanning to see results here.</p>';
        } else {
          recentScansDiv.innerHTML = recentScans{{ $organization->id }}.map(scan => 
            '<div class="list-group-item">' +
              '<strong>' + scan.student.name + '</strong> (' + scan.student.student_id + ')<br>' +
              '<small class="text-muted">Event: ' + scan.event.title + '</small><br>' +
              '<small class="text-muted">Scanned: ' + scan.timestamp + '</small>' +
            '</div>'
          ).join('');
        }
      }
      
      // Cleanup on page unload
      window.addEventListener('beforeunload', function() {
        stopQRScanner();
      });
    })();
  @endforeach
</script>
@endpush
@endsection
