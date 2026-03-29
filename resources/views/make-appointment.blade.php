<div class="page-section">
    <div class="container">
        <h1 class="text-center wow fadeInUp">Make an Appointment with Us!</h1>
        


        <form method="POST" action="{{ route('appointments.store') }}" class="main-form mt-4">
            @csrf
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 py-2">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" autocomplete="name" class="form-control" placeholder="Full name" required>
                </div>
                <div class="col-12 col-md-6 py-2">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" class="form-control" placeholder="Email address" required>
                </div>
                <div class="col-12 col-md-6 py-2">
                    <label for="appointment_date" class="form-label">Set Appointment Date</label>
                    <input type="date" id="appointment_date" name="appointment_date" value="{{ old('appointment_date') }}" autocomplete="off" class="form-control" required>
                </div>
                <div class="col-12 col-md-6 py-2">
                    <label for="appointment_time" class="form-label">Set Appointment Time</label>
                    <select id="appointment_time" name="appointment_time" class="form-control" required>
                        <option value="">Select time</option>
                        @for($hour = 8; $hour <= 15; $hour++)
                            @foreach([0,30] as $minute)
                                @php
                                    $time = sprintf('%02d:%02d', $hour, $minute);
                                    $display = date('g:i A', strtotime($time));
                                    $isBooked = false;
                                    if(isset($bookedSlots) && isset($_GET['appointment_date'])) {
                                        $selectedDate = $_GET['appointment_date'];
                                        foreach($bookedSlots as $slot) {
                                            if($slot['date'] === $selectedDate && $slot['time'] === $time) {
                                                $isBooked = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <option value="{{ $time }}" @selected(old('appointment_time')==$time) @if($isBooked) disabled style="background:#eee;color:#aaa;" @endif>{{ $display }}@if($isBooked) (Booked)@endif</option>
                            @endforeach
                        @endfor
                    </select>
                </div>
                <div class="col-12 col-md-6 py-2">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" autocomplete="tel" class="form-control" placeholder="Contact number" required>
                </div>
                <div class="col-12 col-md-6 py-2">
                    <label for="concern" class="form-label">Concern</label>
                    <select id="concern" name="concern" class="form-control" required>
                        <option value="visitation" @selected(old('concern', 'visitation') == 'visitation')>Visitation</option>
                        @if(isset($concerns))
                            @foreach($concerns as $designation)
                                @if($designation != 'visitation')
                                    <option value="{{ $designation }}" @selected(old('concern') == $designation)>{{ $designation }}</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                </div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restrict date picker to weekdays only
    var dateInput = document.getElementById('appointment_date');
    var timeSelect = document.getElementById('appointment_time');
    var bookedSlots = @json($bookedSlots ?? []);
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            var d = new Date(this.value);
            var day = d.getDay();
            if (day === 0 || day === 6) { // Sunday=0, Saturday=6
                alert('Please select a weekday (Monday to Friday).');
                this.value = '';
                return;
            }
            // Disable booked time slots for selected date
            Array.from(timeSelect.options).forEach(function(opt) {
                opt.disabled = false;
                opt.style.background = '';
                opt.style.color = '';
                var time = opt.value;
                if (!time) return;
                var isBooked = bookedSlots.some(function(slot) {
                    return slot.date === dateInput.value && slot.time === time;
                });
                if (isBooked) {
                    opt.disabled = true;
                    opt.style.background = '#eee';
                    opt.style.color = '#aaa';
                    opt.textContent = opt.textContent.replace(/\s*\(Booked\)/, '') + ' (Booked)';
                } else {
                    opt.textContent = opt.textContent.replace(/\s*\(Booked\)/, '');
                }
            });
        });
    }
});
</script>
@endpush
                <div class="col-12 text-center py-2">
                    <button type="submit" class="btn btn-primary mt-3 wow zoomIn">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>