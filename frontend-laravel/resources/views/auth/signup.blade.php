<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Labventory System</title>
    <style>
        * { box-sizing: border-box; }

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

        .auth-wrapper {
            width: 100%;
            max-width: 980px;
            min-height: 560px;
            background: white;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(34, 60, 120, 0.18);
            display: grid;
            grid-template-columns: 430px 1fr;
        }

        .left {
            padding: 48px 42px;
            background: #eef4ff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand {
            font-size: 14px;
            color: #4262c5;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 36px;
            color: #13213c;
        }

        p {
            color: #5c6784;
            line-height: 1.6;
        }

        .empty {
            margin-top: 22px;
            padding: 18px;
            border: 1px dashed #cfd8f7;
            background: #f7f9ff;
            border-radius: 14px;
            color: #4c5e8c;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            margin-top: 24px;
            text-decoration: none;
            background: #315fe8;
            color: white;
            padding: 13px 18px;
            border-radius: 12px;
            font-weight: bold;
            text-align: center;
        }

        .right {
            position: relative;
            background: linear-gradient(135deg, #1e4ed8, #0c42c9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
            text-align: center;
            padding: 40px;
        }

        .circle {
            position: absolute;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            right: -120px;
            top: -90px;
        }

        .content {
            position: relative;
            z-index: 2;
        }

        .emoji {
            font-size: 110px;
            margin-top: 20px;
        }

        .system-name {
            font-size: 32px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="left">
            <div class="brand">Labventory</div>
            <h1>Sign Up</h1>
            <p>Create an account for Labventory System.</p>

            <div class="empty">
                Sign Up feature is still under development.<br>
                For now, accounts are managed by the Administrator.
            </div>

            <a href="{{ route('login') }}">Back to Sign In</a>
        </div>

        <div class="right">
            <div class="circle"></div>
            <div class="content">
                <div class="system-name">Labventory System</div>
                <p>Account registration will be available after user management is implemented.</p>
                <div class="emoji">🌸</div>
            </div>
        </div>
    </div>
</body>
</html> 