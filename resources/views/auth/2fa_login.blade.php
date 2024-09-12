<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.2fa_verification') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            font-family: 'Nunito', sans-serif;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
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

        .toggle-link {
            display: block;
            text-align: center;
            color: #3182ce;
            cursor: pointer;
            margin-top: 1rem;
        }

        .recovery-input {
            width: calc(3rem * 6 + 0.5rem * 5);
            height: 3rem;
            text-align: center;
            font-size: 1.5rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            transition: all 0.3s ease-in-out;
        }

        .recovery-input:focus {
            border-color: #ff7e5f;
            box-shadow: 0 0 0 3px rgba(255, 126, 95, 0.5);
        }

        .error-message {
            color: #ff0000;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            text-align: center;
        }

        .error {
            text-align: center;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.21.1/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
</head>

<body>
    <div class="flex items-center justify-center min-h-screen">
        <div class="card max-w-md mx-auto animate__animated animate__fadeIn">
            <h1 class="text-4xl font-bold text-center mb-6 text-gray-800">{{ __('messages.2fa_verification') }}</h1>

            <!-- Display Session Message -->
            @if (session('message'))
                <div class="mb-4 text-red-500 text-center font-semibold">
                    {{ session('message') }}
                </div>
            @endif

            <div id="otp-section">
                <p class="text-lg text-center mb-6 text-gray-600">{{ __('messages.enter_code') }}</p>
                <form method="POST" action="{{ route('2fa.verify') }}" class="mb-6" id="otpForm">
                    @csrf
                    <div class="mb-6">
                        <div class="flex justify-center">
                            @for ($i = 0; $i < 6; $i++)
                                <input type="text" class="otp-input" id="one_time_password_{{ $i + 1 }}"
                                    name="one_time_password[]" maxlength="1"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                            @endfor
                        </div>
                        <div id="otpError" class="error-message"></div>
                        <input type="hidden" id="full_one_time_password" name="one_time_password">
                        <input type="hidden" name="secret" value="{{ $secret }}">
                    </div>
                    <button type="submit" class="submit-btn">{{ __('messages.verify_code') }}</button>
                </form>
                <span class="toggle-link" onclick="toggleRecoveryCode()">{{ __('messages.use_recovery_code') }}</span>
            </div>
            <div id="recovery-code-section" style="display: none;">
                <p class="text-lg text-center mb-6 text-gray-600">{{ __('messages.enter_recovery_code') }}</p>
                <form method="POST" action="{{ route('2fa.verifyRecovery') }}" class="mb-6" id="recoveryForm">
                    @csrf
                    <div class="mb-6 flex justify-center">
                        <input type="text" class="recovery-input" id="recovery_code" name="recovery_code"
                            maxlength="16" required>
                    </div>
                    <div id="recoveryError" class="error-message"></div>
                    <button type="submit" class="submit-btn">{{ __('messages.verify_recovery_code') }}</button>
                </form>
                <span class="toggle-link" onclick="toggleRecoveryCode()">{{ __('messages.use_otp') }}</span>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const inputs = document.querySelectorAll('.otp-input');

            // Focus on the first OTP input field when the page loads
            inputs[0].focus();
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

                // Clear previous error message when any OTP input changes
                document.getElementById('otpError').innerHTML = '';
            }

            $('#otpForm').submit(function() {
                const otpValues = Array.from(inputs).map(input => input.value).join('');
                if (otpValues.length !== 6) {
                    document.getElementById('otpError').innerHTML = '{{ __('messages.otp_fields_required') }}';
                    return false;
                }
                return true;
            });

            $('#recoveryForm').validate({
                rules: {
                    recovery_code: {
                        required: true,
                        minlength: 16,
                        maxlength: 16
                    }
                },
                messages: {
                    recovery_code: {
                        required: "{{ __('messages.recovery_code_required') }}",
                        minlength: "{{ __('messages.recovery_code_length') }}",
                        maxlength: "{{ __('messages.recovery_code_length') }}"
                    }
                },
                errorElement: 'div',
                errorPlacement: function(error, element) {
                    error.addClass('text-red-500 text-sm font-semibold');
                    error.insertAfter(element.closest('.mb-6'));
                }
            });
        });

        function toggleRecoveryCode() {
            const otpSection = document.getElementById('otp-section');
            const recoveryCodeSection = document.getElementById('recovery-code-section');
            if (otpSection.style.display === 'none') {
                otpSection.style.display = 'block';
                recoveryCodeSection.style.display = 'none';
            } else {
                otpSection.style.display = 'none';
                recoveryCodeSection.style.display = 'block';
            }
        }
    </script>
</body>

</html>
