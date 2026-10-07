<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PresenSure — Backend API</title>
    <link rel="icon" type="image/webp" href="/MainLogo.webp">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 100%);
            color: #f8fafc;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .container {
            max-width: 640px;
            width: 100%;
            margin: auto;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(37, 99, 235, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
            font-size: 0.8125rem;
            font-weight: 500;
            padding: 0.35rem 0.875rem;
            border-radius: 9999px;
            margin-bottom: 1.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 10px #22c55e;
        }

        .logo-wrap {
            margin-bottom: 1.25rem;
        }

        .logo-wrap svg {
            width: 56px;
            height: 56px;
            color: #3b82f6;
            filter: drop-shadow(0 4px 12px rgba(59, 130, 246, 0.4));
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin-bottom: 0.75rem;
            color: #ffffff;
        }

        h1 span {
            color: #3b82f6;
        }

        p.subtitle {
            font-size: 1.0625rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(51, 65, 85, 0.8);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            font-size: 0.8125rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.75rem;
        }

        .endpoint-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .endpoint {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            background: rgba(15, 23, 42, 0.6);
            padding: 0.6rem 0.85rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(51, 65, 85, 0.5);
        }

        .method {
            font-weight: 700;
            color: #60a5fa;
            margin-right: 0.5rem;
        }

        .path {
            color: #e2e8f0;
        }

        .tag {
            font-size: 0.7rem;
            padding: 0.15rem 0.45rem;
            border-radius: 0.25rem;
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            font-weight: 600;
        }

        footer {
            font-size: 0.875rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="badge">
            <span class="status-dot"></span>
            REST API Active &bull; Laravel {{ app()->version() }}
        </div>

        <div class="logo-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
        </div>

        <h1>Presen<span>Sure</span></h1>
        <p class="subtitle">
            Automated Attendance & Proximity Verification System
        </p>

        <div class="card">
            <div class="card-header">Service Status & Endpoints</div>
            <div class="endpoint-list">
                <div class="endpoint">
                    <div>
                        <span class="method">GET</span>
                        <span class="path">/up</span>
                    </div>
                    <span class="tag">HEALTHY</span>
                </div>
                <div class="endpoint">
                    <div>
                        <span class="method">POST</span>
                        <span class="path">/api/user/signin</span>
                    </div>
                    <span class="tag">AUTH</span>
                </div>
                <div class="endpoint">
                    <div>
                        <span class="method">GET</span>
                        <span class="path">/api/departments</span>
                    </div>
                    <span class="tag">REST API</span>
                </div>
                <div class="endpoint">
                    <div>
                        <span class="method">GET</span>
                        <span class="path">/api/semesters</span>
                    </div>
                    <span class="tag">REST API</span>
                </div>
            </div>
        </div>
    </main>

    <footer>
        &copy; {{ date('Y') }} PresenSure. All rights reserved.
    </footer>
</body>
</html>
