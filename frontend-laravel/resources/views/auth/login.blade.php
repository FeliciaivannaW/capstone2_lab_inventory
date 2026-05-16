<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Labventory System</title>
    <style>
        * {
            box-sizing: border-box;
        }

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
            max-width: 1080px;
            min-height: 620px;
            background: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(34, 60, 120, 0.18);
            display: grid;
            grid-template-columns: 430px 1fr;
        }

        .auth-left {
            background: #eef4ff;
            padding: 48px 42px;
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

        .auth-left h1 {
            margin: 0 0 10px;
            font-size: 36px;
            color: #13213c;
        }

        .subtitle {
            margin: 0 0 28px;
            color: #5c6784;
            line-height: 1.6;
        }

        .alert {
            background: #fde2e2;
            color: #a12626;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .field-label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #1b2740;
        }

        .input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #d5ddf5;
            outline: none;
            font-size: 14px;
            margin-bottom: 18px;
            background: white;
        }

        .input:focus {
            border-color: #4b73ff;
            box-shadow: 0 0 0 4px rgba(75, 115, 255, 0.12);
        }

        .row-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -6px;
            margin-bottom: 22px;
            font-size: 13px;
        }

        .row-links a,
        .bottom-link a {
            color: #315fe8;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #1d4ed8, #315fe8);
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(49, 95, 232, 0.25);
        }

        .btn-primary:hover {
            opacity: 0.95;
        }

        .bottom-link {
            margin-top: 22px;
            text-align: center;
            color: #69748f;
            font-size: 14px;
        }

        .hint-box {
            margin-top: 26px;
            background: #f7f9ff;
            border: 1px dashed #cfd8f7;
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.7;
            color: #5b6886;
        }

        .auth-right {
            position: relative;
            background: linear-gradient(135deg, #1e4ed8, #0c42c9);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .big-circle {
            position: absolute;
            width: 640px;
            height: 640px;
            border-radius: 50%;
            background: rgba(255,255,255,0.11);
            right: -160px;
            top: -60px;
        }

        .mid-circle {
            position: absolute;
            width: 430px;
            height: 430px;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
            right: 40px;
            bottom: -90px;
        }

        .small-bubble {
            position: absolute;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.35);
        }

        .bubble-1 {
            width: 46px;
            height: 46px;
            top: 60px;
            left: 90px;
        }

        .bubble-2 {
            width: 28px;
            height: 28px;
            top: 140px;
            left: 180px;
            border-width: 4px;
        }

        .bubble-3 {
            width: 56px;
            height: 56px;
            bottom: 85px;
            left: 70px;
        }

        .bubble-4 {
            width: 36px;
            height: 36px;
            bottom: 140px;
            right: 130px;
        }

        .bubble-5 {
            width: 24px;
            height: 24px;
            top: 90px;
            right: 90px;
            border-width: 3px;
        }

        .illustration-card {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 40px;
        }

        .illustration-card .system-name {
            font-size: 34px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .illustration-card .desc {
            max-width: 400px;
            margin: 0 auto 28px;
            line-height: 1.7;
            color: rgba(255,255,255,0.88);
        }

        .flower-emoji {
            font-size: 120px;
            line-height: 1;
            filter: drop-shadow(0 12px 24px rgba(0,0,0,0.18));
        }

        .tagline {
            margin-top: 16px;
            font-size: 15px;
            color: rgba(255,255,255,0.92);
        }

        @media (max-width: 900px) {
            .auth-wrapper {
                grid-template-columns: 1fr;
            }

            .auth-right {
                min-height: 280px;
            }

            .auth-left {
                order: 2;
            }

            .auth-right {
                order: 1;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-left">
            <div class="brand">Labventory</div>
            <h1>Sign In</h1>
            <p class="subtitle">
                Welcome back to <strong>Labventory System</strong>.  
                Manage laboratory assets, BHP stock, procurement, and maintenance in one place.
            </p>

            @if(session('error'))
                <div class="alert">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert" style="background:#dcfce7;color:#166534;">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.process') }}">
                @csrf

                <label class="field-label">Email</label>
                <input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>

                <label class="field-label">Password</label>
                <input class="input" type="password" name="password" placeholder="Enter your password" required>

                <div class="row-links">
                    <span></span>
                    <a href="{{ route('forgot.password') }}">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-primary">Sign In</button>
            </form>

            <div class="bottom-link">
                Don’t have an account?
                <a href="{{ route('signup') }}">Sign Up</a>
            </div>

            <div class="hint-box">
                <strong>Demo Accounts</strong><br>
                admin@example.com / password123<br>
                kalab@example.com / password123<br>
                kaprodi@example.com / password123<br>
                stafadmin@example.com / password123<br>
                staflab@example.com / password123
            </div>
        </div>

        <div class="auth-right">
            <div class="big-circle"></div>
            <div class="mid-circle"></div>
            <div class="small-bubble bubble-1"></div>
            <div class="small-bubble bubble-2"></div>
            <div class="small-bubble bubble-3"></div>
            <div class="small-bubble bubble-4"></div>
            <div class="small-bubble bubble-5"></div>

            <div class="illustration-card">
                <div class="system-name">Labventory System</div>
                <p class="desc">
                    A laboratory inventory and consumables management platform
                    for procurement, asset tracking, stock monitoring, and maintenance workflows.
                </p>
                <div class="flower-emoji">🌸</div>
                <div class="tagline">Smart lab operations start here.</div>
            </div>
        </div>
    </div>
</body>
</html>