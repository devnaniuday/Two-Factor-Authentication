<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google 2FA Setup</title>
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
            /* Gray color when disabled */
            cursor: not-allowed;
            /* Change cursor to indicate it's not clickable */
            opacity: 0.7;
            /* Reduce opacity to further indicate disabled state */
        }

        #buttonMessage {
            display: block;
        }

        .submit-btn:not(:disabled)+#buttonMessage {
            display: none;
        }

        .error {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
</head>

<body>
    <div class="container animate__animated animate__fadeIn">
        <div class="left-section">
            <h2 class="text-2xl font-bold text-center mb-4">Recovery Codes</h2>
            <p class="text-lg text-center mb-6 text-gray-600">Please save these recovery codes in a safe place. They
                will help you recover your 2FA account in case you lose access.</p>
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
                    Download Recovery Codes
                </button>
            </div>
            <br>
            <p class="warning-message text-left">
                NOTE:- This is the only time you can see the recovery codes. Please download and keep them safe.
            </p>
        </div>
        <div class="center-section">
            <div class="qr-code mb-4">
                {!! $QR_Image !!}
            </div>
            <p class="text-lg text-center mb-6 text-gray-600">Secret Code: <code
                    class="bg-gray-100 p-1 rounded">{{ $secret }}</code></p>
            <form method="POST" action="{{ route('2fa.store') }}" id="otpForm">
                @csrf
                <div class="otp-input-container">
                    @if (session('message'))
                        <div class="error">
                            {{ session('message') }}
                        </div>
                    @endif

                    <label for="one_time_password"
                        class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2 text-center mt-4 mr-2">Enter
                        the OTP:</label>
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
                        <!-- Hidden input to store concatenated OTP -->
                        <input type="hidden" id="full_one_time_password" name="one_time_password">
                        <input type="hidden" name="secret" value="{{ $secret }}">
                    </div>
                </div>
                <button type="submit" class="submit-btn" id="verifyOtpBtn" disabled>Verify OTP</button>
                <p id="buttonMessage" class="text-sm text-gray-600 mt-2">Verify OTP button will only work after you
                    download your
                    recovery codes</p>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            const inputs = $('.otp-input');
            const verifyOtpBtn = $('#verifyOtpBtn');
            const downloadCodesBtn = $('#downloadCodesBtn');

            inputs.each(function(index) {
                $(this).on('input', function() {
                    if ($(this).val().length === this.maxLength) {
                        if (index < inputs.length - 1) {
                            inputs.eq(index + 1).focus();
                        }
                    }
                    updateFullOTP(); // Update hidden input value
                });
                $(this).on('keydown', function(e) {
                    if (e.key === 'Backspace' && $(this).val().length === 0 && index > 0) {
                        inputs.eq(index - 1).focus();
                    }
                    updateFullOTP(); // Update hidden input value
                });
            });

            function updateFullOTP() {
                const otpValues = inputs.map(function() {
                    return $(this).val();
                }).get().join('');
                $('#full_one_time_password').val(otpValues);
            }

            downloadCodesBtn.on('click', function() {
                const codes = $('.recovery-codes li').map(function() {
                    return $(this).text().trim();
                }).get().join('\n');
                download('recovery-codes.txt', codes);
                verifyOtpBtn.prop('disabled', false); // Enable the Verify OTP button
                alert('Recovery codes downloaded. Now you can proceed to verify your OTP.');
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

            // Initialize jQuery Validation
            $('#otpForm').validate({
                errorElement: 'div',
                errorClass: 'error-message',
                errorPlacement: function(error, element) {
                    error.insertAfter($('#otp-input-container'));
                },
                rules: {
                    'one_time_password[]': {
                        required: true,
                        digits: true,
                        minlength: 6,
                        maxlength: 6,
                        otpValid: true
                    }
                },
                messages: {
                    'one_time_password[]': {
                        required: 'Please enter a 6-digit OTP.',
                        digits: 'Please enter a valid digit.',
                        minlength: 'Please enter a 6-digit OTP.',
                        maxlength: 'Please enter a 6-digit OTP.',
                        otpValid: 'Invalid OTP. Please try again.'
                    }
                },
                submitHandler: function(form) {
                    updateFullOTP(); // Ensure OTP is updated before form submission
                    form.submit();
                }
            });

            // Custom validation method for OTP
            $.validator.addMethod('otpValid', function(value, element) {
                const otpValues = $(element).map(function() {
                    return $(this).val();
                }).get().join('');
                return otpValues.length === 6 && /^\d{6}$/.test(otpValues);
            }, 'Invalid OTP. Please try again.');
        });
    </script>
</body>

</html>
