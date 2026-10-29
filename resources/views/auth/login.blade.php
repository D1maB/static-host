<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow rounded-lg p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Sign in with email code</h1>
        <div id="step1">
            <form id="requestForm">
                @csrf
                <label class="block mb-2">Email</label>
                <input type="email" name="email" required class="w-full border rounded px-3 py-2 mb-4">
                <button class="w-full bg-slate-600 text-white py-2 rounded">Send code</button>
            </form>
        </div>
        <div id="step2" class="hidden">
            <form id="verifyForm">
                @csrf
                <input type="hidden" name="email" id="emailHidden">
                <label class="block mb-2">Code</label>
                <input type="text" name="code" required class="w-full border rounded px-3 py-2 mb-4">
                <button class="w-full bg-slate-600 text-white py-2 rounded">Verify</button>
            </form>
        </div>
        <p id="msg" class="mt-4 text-sm text-gray-600"></p>
    </div>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        // inject meta
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const m = document.createElement('meta'); m.name='csrf-token'; m.content='{{ csrf_token() }}'; document.head.appendChild(m);
        }
        document.getElementById('requestForm').addEventListener('submit', async e => {
            e.preventDefault();
            const email = e.target.email.value;
            const res = await fetch('/login/request', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({email})
            });
            const data = await res.json();
            document.getElementById('msg').textContent = data.message || data.error || '';
            if (res.ok) {
                document.getElementById('emailHidden').value = email;
                document.getElementById('step1').classList.add('hidden');
                document.getElementById('step2').classList.remove('hidden');
            }
        });
        document.getElementById('verifyForm').addEventListener('submit', async e => {
            e.preventDefault();
            const email = document.getElementById('emailHidden').value;
            const code = e.target.code.value;
            const res = await fetch('/login/verify', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({email, code})
            });
            const data = await res.json();
            if (res.ok && data.redirect) {
                window.location.href = data.redirect;
            } else {
                document.getElementById('msg').textContent = data.message || 'Failed';
            }
        });
    </script>
</body>
</html>
