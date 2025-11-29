<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shoe Shop API Tester</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light dark;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .app-shell {
            width: 100%;
            max-width: 1200px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.45);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1px;
            overflow: hidden;
        }

        .panel {
            padding: 2rem;
            background: #0b1222;
        }

        h1 {
            font-size: 1.6rem;
            margin: 0 0 0.5rem;
            font-weight: 600;
        }

        p.subtitle {
            margin: 0 0 1.5rem;
            color: #94a3b8;
        }

        label {
            display: block;
            font-size: 0.88rem;
            font-weight: 500;
            margin-bottom: 0.35rem;
            color: #e2e8f0;
        }

        input,
        select {
            width: 100%;
            background: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            color: #e2e8f0;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            transition: border-color 0.2s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.35);
        }

        .grid-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        button {
            width: 100%;
            background: linear-gradient(135deg, #22d3ee, #2563eb);
            border: none;
            border-radius: 999px;
            padding: 0.85rem 1rem;
            color: #0b1120;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            box-shadow: none;
        }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            font-size: 0.85rem;
            background: rgba(148, 163, 184, 0.12);
            color: #e2e8f0;
        }

        pre {
            margin-top: 1rem;
            background: #020617;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            padding: 1rem;
            overflow: auto;
            max-height: 520px;
        }

        code {
            font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
            font-size: 0.9rem;
            color: #cbd5f5;
        }

        .badge {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            display: inline-block;
            margin-bottom: 0.75rem;
        }

        .danger {
            color: #f87171;
        }

        @media (max-width: 720px) {
            body {
                padding: 1rem;
            }

            .panel {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<main class="app-shell">
    <section class="panel">
        <div class="badge">Quick tester</div>
        <h1>Shoe Shop API Tester</h1>
        <p class="subtitle">Chọn method và nhập endpoint (base URL mặc định là {{ url('/api') }}). Nhấn gửi để xem phản hồi.</p>

        <form id="api-form">
            <div class="grid-row">
                <div>
                    <label for="method">Method</label>
                    <select id="method" name="method">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
                <div>
                    <label for="endpoint">Endpoint</label>
                    <input id="endpoint" name="endpoint" type="text" value="/giay" placeholder="/giay">
                </div>
            </div>

            <button type="submit" id="sendBtn">Gửi request</button>
        </form>
    </section>

    <section class="panel">
        <div class="badge">Response</div>
        <h1>Kết quả</h1>
        <p class="subtitle">Response status, thời gian phản hồi và nội dung chi tiết.</p>

        <div class="meta" id="responseMeta">
            <span class="chip">Chưa gửi request</span>
        </div>
        <pre><code id="responseBody">Chưa có dữ liệu.</code></pre>
    </section>
</main>

<script>
    const form = document.getElementById('api-form');
    const responseBodyEl = document.getElementById('responseBody');
    const responseMetaEl = document.getElementById('responseMeta');
    const sendBtn = document.getElementById('sendBtn');
    const defaultBaseUrl = "{{ url('/api') }}";

    const escapeHtml = (value = '') => value
        .toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const baseUrl = defaultBaseUrl.replace(/\/$/, '');
        const endpointRaw = form.endpoint.value.trim();
        const endpoint = endpointRaw.length ? (endpointRaw.startsWith('/') ? endpointRaw : `/${endpointRaw}`) : '';
        const url = `${baseUrl}${endpoint}`;
        const method = form.method.value;

        const headers = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        };

        sendBtn.disabled = true;
        sendBtn.textContent = 'Đang gửi...';
        responseMetaEl.innerHTML = '<span class="chip">Đang chờ phản hồi...</span>';
        responseBodyEl.textContent = 'Đang gửi request...';

        const startedAt = performance.now();

        try {
            const response = await fetch(url, {
                method,
                headers,
            });

            const duration = (performance.now() - startedAt).toFixed(0);
            const responseText = await response.text();
            let formattedBody = responseText;

            try {
                const json = JSON.parse(responseText);
                formattedBody = JSON.stringify(json, null, 2);
            } catch (_) {
                // giữ nguyên responseText nếu không phải JSON
            }

            responseMetaEl.innerHTML = [
                `<span class="chip">${escapeHtml(method)}</span>`,
                `<span class="chip ${response.ok ? '' : 'danger'}">Status: ${escapeHtml(response.status)}</span>`,
                `<span class="chip">${escapeHtml(duration)} ms</span>`,
                `<span class="chip">${escapeHtml(url)}</span>`,
            ].join('');

            responseBodyEl.textContent = formattedBody || 'Response rỗng.';
        } catch (error) {
            responseMetaEl.innerHTML = '<span class="chip danger">Request failed</span>';
            responseBodyEl.textContent = error.message;
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Gửi request';
        }
    });
</script>
</body>
</html>
