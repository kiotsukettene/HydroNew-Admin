<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reply to Your Feedback</title>
</head>
<body style="margin: 0; padding: 5px; font-family: 'Arial', sans-serif; background-color: #f4f4f4;">
    <div style="max-width: 500px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; padding: 40px 50px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">

        <div style="margin-bottom: 30px;">
            <img src="{{ asset('hydro-logo.png') }}" alt="HydroNew Logo" style="width:120px; height:auto;">
        </div>

        <div style="margin-bottom: 2px;">
            <img src="{{ asset('email-svg2.png') }}" alt="Mail Icon" style="width: 100px; height: auto;">
        </div>

        <h2 style="color: #2E2E2E; font-size: 26px; margin-bottom: 10px;">Response to Your Feedback</h2>

        <p style="color: #555; font-size: 16px; margin-bottom: 25px;">
            Hello{{ $feedback->user ? ' ' . $feedback->user->first_name : '' }}! Thank you for sharing your feedback with us.
        </p>

        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 25px; margin: 25px 0; border-left: 4px solid #445104;">
            <h3 style="color: #445104; font-size: 18px; margin: 0 0 15px 0; text-align: left;">Our Response:</h3>
            <p style="color: #2E2E2E; font-size: 15px; line-height: 1.6; text-align: left; white-space: pre-wrap; word-wrap: break-word; margin: 0;">{{ $replyMessage }}</p>
        </div>

        <div style="border-top: 1px solid #e5e7eb; margin: 30px 0;"></div>

        <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; text-align: left;">
            <h3 style="color: #777; font-size: 16px; margin: 0 0 15px 0;">Your Original Message</h3>

            <div style="margin-bottom: 10px;">
                <span style="color: #555; font-size: 14px;">
                    <strong>Category:</strong>
                    <span style="background-color: #e8f4f8; color: #445104; padding: 3px 10px; border-radius: 10px; font-size: 12px; font-weight: 600;">
                        @switch($feedback->category)
                            @case('bug_report')
                                Bug Report
                                @break
                            @case('feature_request')
                                Feature Request
                                @break
                            @case('general_feedback')
                                General Feedback
                                @break
                            @case('device_issue')
                                Device Issue
                                @break
                            @default
                                Other
                        @endswitch
                    </span>
                </span>
            </div>

            @if($feedback->subject)
            <div style="margin-bottom: 10px;">
                <span style="color: #555; font-size: 14px;">
                    <strong>Subject:</strong> {{ $feedback->subject }}
                </span>
            </div>
            @endif

            @if($feedback->device)
            <div style="margin-bottom: 10px;">
                <span style="color: #555; font-size: 14px;">
                    <strong>Device:</strong> {{ $feedback->device->device_name }} ({{ $feedback->device->serial_number }})
                </span>
            </div>
            @endif

            <div style="margin-bottom: 10px;">
                <span style="color: #555; font-size: 14px;">
                    <strong>Submitted:</strong> {{ $feedback->created_at->format('M d, Y \a\t g:i A') }}
                </span>
            </div>

            <div style="margin-top: 15px;">
                <p style="color: #555; font-size: 14px; margin: 5px 0;"><strong>Your Message:</strong></p>
                <p style="color: #555; font-size: 14px; line-height: 1.6; white-space: pre-wrap; word-wrap: break-word; margin: 10px 0;">{{ $feedback->message }}</p>
            </div>
        </div>

        <p style="color: #777; font-size: 14px; margin: 25px 0; text-align: justify;">
            If you have any further questions or concerns, please don't hesitate to reach out to us.
        </p>

        <p style="color: #2E2E2E; font-size: 16px; margin-top: 40px;">
            Best regards,<br>
            <strong>HydroNew Team</strong>
        </p>

        <p style="font-size: 12px; color: #999; margin-top: 30px;">
            © {{ date('Y') }} HydroNew. All rights reserved.
        </p>
    </div>
</body>
</html>
