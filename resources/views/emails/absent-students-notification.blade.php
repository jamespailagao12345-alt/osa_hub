<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absent Students Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #dc3545; margin-top: 0;">Absent Students Notification</h2>
        <p style="margin-bottom: 5px;"><strong>Event:</strong> {{ $event->name }}</p>
        <p style="margin-bottom: 5px;"><strong>Date:</strong> {{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('M d, Y h:i A') : 'N/A' }}</p>
        <p style="margin-bottom: 0;"><strong>Location:</strong> {{ $event->location ?? 'N/A' }}</p>
    </div>

    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px;">
        <p style="margin: 0;"><strong>Action Required:</strong> The following students were marked as <strong>Absent</strong> because they were not scanned during the event participation monitoring period. Please send excuse letter requests to these students.</p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <h3 style="color: #495057; margin-top: 0;">Absent Students ({{ count($absentStudents) }})</h3>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;">Name</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;">Student ID</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6;">Email</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absentStudents as $student)
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 10px;">{{ $student['name'] }}</td>
                    <td style="padding: 10px;">{{ $student['student_id'] }}</td>
                    <td style="padding: 10px;">{{ $student['email'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="background-color: #e7f3ff; border-left: 4px solid #0d6efd; padding: 15px; margin-bottom: 20px;">
        <p style="margin: 0;"><strong>Next Steps:</strong></p>
        <ul style="margin: 10px 0 0 20px; padding: 0;">
            <li>Contact each student to request an excuse letter</li>
            <li>Document the reason for their absence</li>
            <li>Update student records as necessary</li>
        </ul>
    </div>

    <div style="text-align: center; color: #6c757d; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
        <p style="margin: 0;">This is an automated notification from OSA Hub Event Participation Monitoring System.</p>
    </div>
</body>
</html>

