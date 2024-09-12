<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('google_2fa_setup') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            font-family: 'Nunito', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 80%;
            max-width: 1200px;
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .left-section,
        .center-section,
        .right-section {
            flex: 1;
            margin: 0 1rem;
        }

        .recovery-codes {
            background-color: #f0f4f8;
            padding: 1rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            margin-bottom: 2rem;
            overflow-y: auto;
            max-height: 200px;
        }

        .recovery-codes ul {
            list-style-type: none;
            padding: 0;
        }

        .recovery-codes li {
            background-color: #e2e8f0;
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
        }

        .recovery-codes li::before {
            content: '\2022';
            color: #ff7e5f;
            font-weight: bold;
            display: inline-block;
            width: 1rem;
            margin-right: 0.5rem;
        }

        .otp-input-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .otp-input {
            width: 3rem;
            height: 3rem;
            text-align: center;
            font-size: 1.5rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            margin-right: 0.5rem;
            transition: all 0.3s ease-in-out;
        }

        .otp-input:focus {
            border-color: #ff7e5f;
            box-shadow: 0 0 0 3px rgba(255, 126, 95, 0.5);
        }

        .submit-btn {
            width: 100%;
            background-color: #f56565;
            color: white;
            font-weight: bold;
            padding: 12px 20px;
            font-size: 1.2rem;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            outline: none;
        }

        .submit-btn:hover {
            background-color: #e53e3e;
        }

        .qr-code {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .warning-message {
            color: #f56565;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1rem;
        }

        .submit-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            opacity: 0.7;
        }

        #buttonMessage {
            display: block;
        }

        .submit-btn:not(:disabled)+#buttonMessage {
            display: none;
        }

        .error-message {
            color: #e53e3e;
            font-weight: bold;
            text-align: center;
            margin-top: 1rem;
        }

        .session-message {
            text-align: center;
            font-weight: bold;
            margin: 1rem 0;
        }

        .session-message.success {
            color: #38a169;
        }

        .session-message.error {
            color: #e53e3e;
        }
    </style>
</head>

<body>
    <div class="container animate__animated animate__fadeIn">
        <div class="left-section">
            <h2 class="text-2xl font-bold text-center mb-4">{{ __('messages.recovery_codes') }}</h2>
            <p class="text-lg text-center mb-6 text-gray-600">{{ __('messages.recovery_codes_description') }}</p>
            <div class="recovery-codes">
                <ul>
                    @foreach ($recoveryCodes as $code)
                        <li>{{ $code }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="text-center">
                <button id="downloadCodesBtn"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('messages.download_recovery_codes') }}
                </button>
            </div>
            <br>
            <p class="warning-message text-left">
                {{ __('messages.note_recovery_codes') }}
            </p>
        </div>
        <div class="center-section">
            <div class="qr-code mb-4">
                {!! $QR_Image !!}
            </div>
            <p class="text-lg text-center mb-6 text-gray-600">{{ __('messages.secret_code') }}: <code
                    class="bg-gray-100 p-1 rounded">{{ $secret }}</code></p>
            <form method="POST" action="{{ route('2fa.store') }}" id="otpForm">
                @csrf
                <div class="otp-input-container">
                    <label for="one_time_password"
                        class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2 text-center mt-4 mr-2">{{ __('messages.enter_otp') }}:</label>
                    <div>
                        <input type="text" class="otp-input" id="one_time_password_1" name="one_time_password[]"
                            maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <input type="text" class="otp-input" id="one_time_password_2" name="one_time_password[]"
                            maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <input type="text" class="otp-input" id="one_time_password_3" name="one_time_password[]"
                            maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <input type="text" class="otp-input" id="one_time_password_4" name="one_time_password[]"
                            maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <input type="text" class="otp-input" id="one_time_password_5" name="one_time_password[]"
                            maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <input type="text" class="otp-input" id="one_time_password_6" name="one_time_password[]"
                            maxlength="1" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                        <input type="hidden" id="full_one_time_password" name="one_time_password">
                        <input type="hidden" name="secret" value="{{ $secret }}">
                    </div>
                </div>
                <button type="submit" class="submit-btn" id="verifyOtpBtn" disabled>{{ __('messages.verify_otp') }}</button>
                <p id="buttonMessage" class="text-sm text-gray-600 mt-2">{{ __('messages.verify_otp_button_message') }}</p>
            </form>
            @if (session('success'))
                <p class="session-message success">{{ __('messages.success_message') }}</p>
            @elseif(session('error'))
                <p class="session-message error">{{ __('messages.error_message') }}</p>
            @endif
            <div class="error-message" id="errorMessage"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.21.1/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const inputs = document.querySelectorAll('.otp-input');
            // Focus on the first OTP input field when the page loads
            inputs[0].focus();
            const verifyOtpBtn = document.getElementById('verifyOtpBtn');
            const downloadCodesBtn = document.getElementById('downloadCodesBtn');
            const errorMessage = document.getElementById('errorMessage');

            inputs.forEach((input, index) => {
                input.addEventListener('input', () => {
                    if (input.value.length === input.maxLength) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    }
                    updateFullOTP(); // Update hidden input value
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                        inputs[index - 1].focus();
                    }
                    updateFullOTP(); // Update hidden input value
                });
            });

            function updateFullOTP() {
                const otpValues = Array.from(inputs).map(input => input.value).join('');
                document.getElementById('full_one_time_password').value = otpValues;
            }

            downloadCodesBtn.addEventListener('click', () => {
                const codes = Array.from(document.querySelectorAll('.recovery-codes li')).map(li => li
                    .textContent.trim()).join('\n');
                download('recovery-codes.txt', codes);
                verifyOtpBtn.disabled = false; // Enable the Verify OTP button
                alert('{{ __('messages.recovery_codes_downloaded_alert') }}');
            });

            function download(filename, text) {
                const element = document.createElement('a');
                element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
                element.setAttribute('download', filename);
                element.style.display = 'none';
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
            }

            $('#otpForm').submit(function(event) {
                const otpValues = Array.from(inputs).map(input => input.value).join('');
                if (otpValues.length !== 6) {
                    errorMessage.textContent = '{{ __('messages.please_enter_valid_otp') }}';
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
