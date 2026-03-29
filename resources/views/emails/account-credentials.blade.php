<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSA Hub - Account Credentials</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f4f4f4; padding: 20px; border-radius: 5px;">
        <h2 style="color: #333; margin-top: 0;">OSA Hub - Account Credentials</h2>
        
        <p>Hello {{ $name }},</p>
        
        <p>Your {{ $roleName }} account has been created in the OSA Hub system.</p>
        
        <div style="background-color: #fff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">
            <p style="margin: 0;"><strong>Username:</strong> {{ $email }}</p>
            <p style="margin: 10px 0 0 0;"><strong>Password:</strong> {{ $password }}</p>
        </div>
        
        <p><strong>Important:</strong> Please log in with these credentials and change your password immediately for security purposes.</p>
        
        <p>You can access the system at: <a href="{{ url('/login') }}" style="color: #007bff;">{{ url('/login') }}</a></p>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            If you did not expect this email, please contact the system administrator.
        </p>
        
        <p style="margin-top: 20px; color: #666; font-size: 12px;">
            Best regards,<br>
            OSA Hub System
        </p>
    </div>
</body>
</html>
