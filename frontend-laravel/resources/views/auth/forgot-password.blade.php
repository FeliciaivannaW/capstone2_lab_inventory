<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Labventory System</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #dfe9ff, #c9d9ff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 520px;
            background: white;
            padding: 36px;
            border-radius: 24px;
            box-shadow: 0 18px 40px rgba(34, 60, 120, 0.15);
            text-align: center;
        }

        h1 {
            margin-top: 0;
            color: #1b2740;
        }

        p {
            color: #5f6c89;
            line-height: 1.7;
        }

        .empty {
            margin: 24px 0;
            padding: 20px;
            border: 1px dashed #cfd8f7;
            background: #f7f9ff;
            border-radius: 16px;
            color: #4c5e8c;
        }

        a {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: white;
            background: #315fe8;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Forgot Password</h1>
        <p>Password recovery page for Labventory System.</p>

        <div class="empty">
            This feature is not available yet.<br>
            You can add email reset flow later if needed.
        </div>

        <a href="{{ route('login') }}">Back to Sign In</a>
    </div>
</body>
</html>