@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="container-fluid">
  <div class="row">
    @include('admin.partials.sidebar')
    <main id="adminMain" class="col-md-10 py-4">
      <div class="admin-back-btn-wrap">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
      </div>
      
      <h2 class="mb-4">Scan QR Code</h2>
      
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="qrEventSelect" class="form-label">Select Event:</label>
              <select id="qrEventSelect" class="form-control">
                <option value="">-- Select an event --</option>
                @foreach($events as $event)
                  <option value="{{ $event->id }}">{{ $event->name }} - {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y') }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label for="qrScannerInput" class="form-label" style="cursor: pointer;" id="qrLabel">
                QR Code Data: <small class="text-muted">(click here or Upload button to select file)</small>
              </label>
              <div class="input-group">
                <input type="text" id="qrScannerInput" class="form-control" placeholder="Scan QR code, paste QR data, or click 'Upload' to select file" autocomplete="off">
                <input type="file" id="qrFileUpload" class="d-none" accept="image/*,.txt,.json" />
                <button type="button" id="uploadQrBtn" class="btn btn-info" title="Upload QR code file from device">
                  <i class="bi bi-upload"></i> Upload
                </button>
                <button type="button" id="startCameraBtn" class="btn btn-primary">
                  <i class="bi bi-camera"></i> Start Camera
                </button>
                <button type="button" id="stopCameraBtn" class="btn btn-secondary" style="display: none;">
                  <i class="bi bi-camera-video-off"></i> Stop Camera
                </button>
              </div>
              <small class="form-text text-muted">Click "Upload" to select a QR code image (PNG, JPG) or text file (.txt, .json) from your device</small>
            </div>
          </div>
          
          <!-- Camera Preview -->
          <div id="cameraPreview" class="mb-3" style="display: none;">
            <video id="qrVideo" width="100%" height="300" autoplay playsinline style="border: 2px solid midnightblue; border-radius: 4px;"></video>
            <canvas id="qrCanvas" style="display: none;"></canvas>
          </div>
          
          <!-- Scan Results -->
          <div id="scanResults" class="mt-3"></div>
          
          <!-- Recent Scans -->
          <div class="mt-4">
            <h6>Recent Scans</h6>
            <div id="recentScans" class="list-group" style="max-height: 300px; overflow-y: auto;">
              <p class="text-muted">No scans yet. Start scanning to see results here.</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
  (function() {
    let qrStream = null;
    let qrScanInterval = null;
    let isProcessing = false; // Flag to prevent multiple simultaneous scans
    const recentScans = [];
    
    const qrScannerInput = document.getElementById('qrScannerInput');
    const qrEventSelect = document.getElementById('qrEventSelect');
    const qrFileUpload = document.getElementById('qrFileUpload');
    const uploadQrBtn = document.getElementById('uploadQrBtn');
    const qrLabel = document.getElementById('qrLabel');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const stopCameraBtn = document.getElementById('stopCameraBtn');
    const cameraPreview = document.getElementById('cameraPreview');
    const qrVideo = document.getElementById('qrVideo');
    const qrCanvas = document.getElementById('qrCanvas');
    const scanResults = document.getElementById('scanResults');
    const recentScansDiv = document.getElementById('recentScans');
    
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
          qrStream = stream;
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
            qrScanInterval = setInterval(function() {
              if (isProcessing) return; // Skip if already processing
              
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
                    isProcessing = true;
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
      isProcessing = false; // Reset processing flag
      if (qrStream) {
        qrStream.getTracks().forEach(track => track.stop());
        qrStream = null;
      }
      if (qrScanInterval) {
        clearInterval(qrScanInterval);
        qrScanInterval = null;
      }
      cameraPreview.style.display = 'none';
      if (startCameraBtn) startCameraBtn.style.display = 'inline-block';
      if (stopCameraBtn) stopCameraBtn.style.display = 'none';
      qrVideo.srcObject = null;
    }
    
    function processQRCode(qrData) {
      if (!qrData || qrData.trim() === '') {
        isProcessing = false;
        showMessage('Please enter or scan a QR code.', 'warning');
        return;
      }
      
      const eventId = qrEventSelect.value;
      if (!eventId) {
        isProcessing = false;
        showMessage('Please select an event first.', 'warning');
        return;
      }
      
      scanResults.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Processing QR code...</div>';
      
      fetch('{{ route("admin.qr.scan") }}', {
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
        isProcessing = false; // Reset flag after processing
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
        isProcessing = false; // Reset flag on error
        console.error('QR scan error:', error);
        showMessage('An error occurred while processing the QR code.', 'danger');
      });
    }
    
    function showMessage(message, type) {
      scanResults.innerHTML = '<div class="alert alert-' + type + '">' + message + '</div>';
    }
    
    function addRecentScan(student, event, timestamp) {
      recentScans.unshift({ student, event, timestamp });
      if (recentScans.length > 10) recentScans.pop();
      
      if (recentScans.length === 0) {
        recentScansDiv.innerHTML = '<p class="text-muted">No scans yet. Start scanning to see results here.</p>';
      } else {
        recentScansDiv.innerHTML = recentScans.map(scan => 
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
</script>
@endpush
@endsection

