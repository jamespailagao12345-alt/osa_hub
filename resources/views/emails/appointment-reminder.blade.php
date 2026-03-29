<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: midnightblue;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid midnightblue;
        }
        .reminder-notice {
            background-color: #fff3cd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Appointment Reminder</h2>
        </div>
        <div class="content">
            <p>Dear {{ $staff->first_name }} {{ $staff->last_name }},</p>
            
            <div class="reminder-notice">
                <p><strong>⏰ Reminder: You have an appointment in 1 hour!</strong></p>
            </div>
            
            @if($isRescheduled)
                <p>This is a reminder for a <strong>rescheduled appointment</strong> that you have coming up.</p>
            @else
                <p>This is a reminder for an <strong>approved appointment</strong> that you have coming up.</p>
            @endif
            
            <div class="details">
                <p><strong>Appointment Details:</strong></p>
                <p><strong>Requester:</strong> {{ $appointment->full_name }}</p>
                <p><strong>Email:</strong> {{ $appointment->email }}</p>
                <p><strong>Contact Number:</strong> {{ $appointment->contact_number ?? 'N/A' }}</p>
                @if($isRescheduled && $appointment->rescheduled_date)
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($appointment->rescheduled_date)->format('F d, Y') }}</p>
                    <p><strong>Time:</strong> {{ $appointment->rescheduled_time ? date('g:i A', strtotime($appointment->rescheduled_time)) : 'To be determined' }}</p>
                @else
                    <p><strong>Date:</strong> {{ $appointment->appointment_date->format('F d, Y') }}</p>
                    <p><strong>Time:</strong> {{ $appointment->appointment_time ? date('g:i A', strtotime($appointment->appointment_time)) : 'To be determined' }}</p>
                @endif
                @if($appointment->concern)
                <p><strong>Concern:</strong> {{ $appointment->concern }}</p>
                @endif
                @if($appointment->category)
                <p><strong>Category:</strong> {{ $appointment->category }}</p>
                @endif
            </div>
            
            <p><strong>Please be ready for this appointment. The requester is expecting to meet with you shortly.</strong></p>
            
            <p>If you need to reschedule or have any questions, please contact the requester or update the appointment status in the system.</p>
            
            <p>Thank you for your attention.</p>
            
            <p>Best regards,<br>
            <strong>OSA Balubal Team</strong></p>
        </div>
    </div>
</body>
</html>

