<div class="page-section">
    <div class="container">
      <h1 class="text-center mb-5 wow fadeInUp">Meet the OSA Staff</h1>

  <!-- The ID 'doctorSlideshow' is fine as long as your JS initializes the Owl Carousel targeting this ID. -->
  <div class="owl-carousel wow fadeInUp" id="staffSlideshow">
        
        @foreach($staff as $staffMember)
          @if($staffMember->image)
          <div class="item">
            <div class="card-staff">
              <div class="header">
                <img height="300px" 
                     src="{{ \Illuminate\Support\Facades\Storage::url($staffMember->image) }}" 
                     alt="{{ $staffMember->first_name }} {{ $staffMember->last_name }}" 
                     style="cursor: pointer;"
                     class="staff-image-clickable"
                     data-staff-id="{{ $staffMember->id }}"
                     data-staff-first-name="{{ $staffMember->first_name }}"
                     data-staff-middle-name="{{ $staffMember->middle_name ?? '' }}"
                     data-staff-last-name="{{ $staffMember->last_name }}"
                     data-staff-designation="{{ $staffMember->designation ?? 'N/A' }}"
                     data-staff-image="{{ \Illuminate\Support\Facades\Storage::url($staffMember->image) }}">
                <div class="meta">
                  <a href="#"><span class="mai-call"></span></a>
                  <a href="#"><span class="mai-logo-whatsapp"></span></a>
                </div>
              </div>
              <div class="body">
                <p class="text-xl mb-0">{{ $staffMember->first_name }} {{ $staffMember->middle_name ?? '' }} {{ $staffMember->last_name }}</p>
                <span class="text-sm text-grey">{{ $staffMember->designation ?? 'N/A' }}</span>
              </div>
            </div>
          </div>
          @endif
        @endforeach
      </div>
      <!-- Carousel navigation buttons -->
      <div class="text-center mt-4">
        <button id="prevStaff" class="btn btn-secondary btn-lg mx-2">Previous Staff</button>
        <button id="nextStaff" class="btn btn-primary btn-lg mx-2">Next Staff</button>
      </div>
    </div>
  </div>

  <!-- Staff Details Modal -->
  <div class="modal fade" id="staffDetailsModal" tabindex="-1" aria-labelledby="staffDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staffDetailsModalLabel">Staff Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 text-center mb-3">
              <img id="modalStaffImage" src="" alt="Staff Image" class="img-fluid rounded-circle" style="width: 200px; height: 200px; object-fit: cover; border: 3px solid midnightblue;">
            </div>
            <div class="col-md-8">
              <h4 id="modalStaffName" class="mb-2"></h4>
              <p id="modalStaffDesignation" class="text-muted mb-3"></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Add click event listeners to all staff images
      document.querySelectorAll('.staff-image-clickable').forEach(function(img) {
        img.addEventListener('click', function() {
          const firstName = this.getAttribute('data-staff-first-name');
          const middleName = this.getAttribute('data-staff-middle-name');
          const lastName = this.getAttribute('data-staff-last-name');
          const designation = this.getAttribute('data-staff-designation');
          const imageUrl = this.getAttribute('data-staff-image');
          
          const fullName = firstName + (middleName ? ' ' + middleName : '') + ' ' + lastName;
          document.getElementById('modalStaffName').textContent = fullName;
          document.getElementById('modalStaffDesignation').textContent = designation;
          
          const imgElement = document.getElementById('modalStaffImage');
          if (imageUrl && imageUrl.trim() !== '') {
            imgElement.src = imageUrl;
            imgElement.style.display = 'block';
          } else {
            imgElement.style.display = 'none';
          }
          
          // Show modal using Bootstrap 5
          const modalElement = document.getElementById('staffDetailsModal');
          const modal = new bootstrap.Modal(modalElement);
          modal.show();
        });
      });
    });
  </script>