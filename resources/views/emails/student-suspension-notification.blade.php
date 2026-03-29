<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspension Notice</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #dc3545; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Account Suspension Notice</h2>
    </div>

    <div style="background-color: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; margin-bottom: 20px;">
        <p>Dear {{ $student->first_name }} {{ $student->last_name }},</p>
        
        <p>Your account has been <strong>suspended</strong> due to the following reason:</p>
        
        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;"><strong>{{ $reason }}</strong></p>
        </div>

        <p><strong>Action Required:</strong></p>
        <p>Please see the OSA Head to discuss your account status and resolve this matter.</p>
        
        <p>Your account will remain suspended until you meet with the OSA Head and the suspension is lifted.</p>
    </div>

    <div style="background-color: #e7f3ff; border-left: 4px solid #0d6efd; padding: 15px; margin-bottom: 20px;">
        <p style="margin: 0;"><strong>Contact Information:</strong></p>
        <p style="margin: 5px 0 0 0;">Please visit the OSA office or contact your OSA representative for assistance.</p>
    </div>

    <div style="text-align: center; color: #6c757d; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
        <p style="margin: 0;">This is an automated notification from OSA Hub Event Participation Monitoring System.</p>
    </div>
</body>
</html>

