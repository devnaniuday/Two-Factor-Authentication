<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.recovery_codes') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #1a202c;
            transition: background-color 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            background-color: #2d3748;
        }

        .btn {
            background-color: #4a5568;
            color: #cbd5e0;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #2d3748;
        }
    </style>
</head>

<body class="bg-gray-900 text-white">
    <div class="container py-6">
        <div class="bg-yellow-300 text-yellow-900 p-4 rounded mb-4">
            <p>{{ __('messages.store_recovery_codes_securely') }}</p>
        </div>
        <h1 class="text-3xl font-bold mb-4">{{ __('messages.your_recovery_codes') }}</h1>
        <ul id="recoveryCodes" class="list-disc pl-5">
            @foreach ($recoveryCodes as $code)
                <li>{{ $code }}</li>
            @endforeach
        </ul>
        <div class="flex justify-end mt-4">
            <a href="{{ route('dashboard') }}"
                class="mr-2 inline-block px-4 py-2 btn">{{ __('messages.back_to_dashboard') }}</a>
            {{-- <button class="mr-2 inline-block px-4 py-2 btn"
                onclick="copyToClipboard()">{{ __('messages.copy_to_clipboard') }}</button> --}}
            <button class="mr-2 inline-block px-4 py-2 btn"
                onclick="downloadRecoveryCodes()">{{ __('messages.download_recovery_codes') }}</button>
        </div>
    </div>

    <script>
        function downloadRecoveryCodes() {
            let codes = '';
            @foreach ($recoveryCodes as $code)
                codes += '{{ $code }}\n';
            @endforeach
            const blob = new Blob([codes], {
                type: 'text/plain'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'recovery_codes.txt';
            a.click();
        }

        function copyToClipboard() {
            let codes = '';
            @foreach ($recoveryCodes as $code)
                codes += '{{ $code }}\n';
            @endforeach
            navigator.clipboard.writeText(codes).then(function() {
                alert('{{ __('messages.codes_copied') }}');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>

    <script>
        window.onload = function() {
            history.pushState(null, '', location.href);
            window.onpopstate = function() {
                history.go(1);
            };
        };

        // Detect page refresh
        if (performance.navigation.type === 1) {
            window.location.href = '{{ route('dashboard') }}';
        }
    </script>
</body>

</html>
