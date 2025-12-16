<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header {
            background-color: #ffffff;
            padding: 32px 40px;
            border-bottom: 1px solid #f3f4f6;
            text-align: center;
        }

        .content {
            padding: 40px;
        }

        .footer {
            background-color: #f9fafb;
            padding: 24px 40px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }

        /* Typography */
        h1 {
            color: #111827;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 16px;
        }

        p {
            color: #4b5563;
            font-size: 16px;
            line-height: 24px;
            margin: 0 0 24px;
        }

        .text-small {
            font-size: 14px;
            color: #6b7280;
        }

        /* Button */
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }

        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #4338ca;
        }

        /* Logo components */
        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #4f46e5;
            margin-left: 8px;
            font-family: sans-serif;
        }

        .logo-text span {
            color: #1f2937;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header with Logo -->
            <div class="header">
                <a href="{{ config('app.url') }}" class="logo">
                    <!-- SVG Logo from Dashboard -->
                    <img src="https://ui-avatars.com/api/?name=H+R&background=4f46e5&color=fff&size=48&rounded=true"
                        alt="HAHAHRMS" width="48" height="48" style="vertical-align: middle;">
                    <span class="logo-text">HAHA<span>HRMS</span></span>
                </a>
            </div>

            <!-- Content -->
            <div class="content">
                <h1>Reset Your Password</h1>
                <p>Hello,</p>
                <p>You satisfy the criteria for a password reset request for your account. If you didn't ask for this,
                    you can just ignore this email.</p>
                <p>To reset your password, click the button below:</p>

                <div class="btn-container">
                    <a href="{{ $url }}" class="btn">Reset Password</a>
                </div>

                <p>This password reset link will expire in {{ $count }} minutes.</p>

                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">

                <p class="text-small">If you're having trouble clicking the "Reset Password" button, copy and paste the
                    URL below into your web browser:</p>
                <p class="text-small" style="word-break: break-all; color: #4f46e5;">{{ $url }}</p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>

</html>