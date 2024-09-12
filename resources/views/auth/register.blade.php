<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script src="https://www.google.com/recaptcha/api.js?render=6Lf8LBAqAAAAAICDNaJ-MzBoKGTxG8dLND9Bfeaq"></script>
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
            flex-direction: column;
            justify-content: center;
            width: 400px;
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-label {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            transition: all 0.3s ease-in-out;
            padding-right: 2.5rem;
        }

        .form-input:focus {
            border-color: #ff7e5f;
            box-shadow: 0 0 0 3px rgba(255, 126, 95, 0.5);
        }

        .icon {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            transform: translateY(-50%);
            font-size: 1rem;
            transition: all 0.3s ease;
            color: #ccc;
        }

        .icon-valid {
            color: #38a169;
            transform: unset;
        }

        .icon-invalid {
            color: #e53e3e;
        }

        .icon::before {
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
        }

        .icon-valid::before {
            content: '\f00c';
        }

        .icon-invalid::before {
            content: '\f00d';
        }

        .submit-btn {
            width: 100%;
            background-color: #ff7e5f;
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

        .error-message {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            transition: all 0.3s ease;
            height: 0;
            overflow: hidden;
        }

        .error-message.show {
            height: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="container animate__animated animate__fadeIn">
        <h2 class="text-2xl font-bold text-center mb-4">{{ __('messages.register') }}</h2>
        <form id="registerForm" method="POST" action="{{ route('register') }}">
            @csrf
            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
            <div class="form-group">
                <label for="name" class="form-label">{{ __('messages.name') }}</label>
                <input type="text" id="name" name="name" class="form-input">
                <span id="nameError" class="error-message"></span>
                @error('name')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                <span id="nameIcon" class="icon"></span>
            </div>
            <div class="form-group">
                <label for="email" class="form-label">{{ __('messages.email') }}</label>
                <input type="email" id="email" name="email" class="form-input">
                <span id="emailError" class="error-message"></span>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                <span id="emailIcon" class="icon"></span>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">{{ __('messages.password') }}</label>
                <input type="password" id="password" name="password" class="form-input">
                <span id="passwordError" class="error-message"></span>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                <span id="passwordIcon" class="icon"></span>
            </div>
            <div class="form-group">
                <label for="password_confirmation" class="form-label">{{ __('messages.confirm_password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input">
                <span id="passwordConfirmError" class="error-message"></span>
                @error('password_confirmation')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                <span id="passwordConfirmIcon" class="icon"></span>
            </div>
            <button type="submit" class="submit-btn">{{ __('messages.register_button') }}</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#name').focus(); // Set focus on the first input field on page load

            const fields = [{
                    input: $('#name'),
                    error: $('#nameError'),
                    icon: $('#nameIcon')
                },
                {
                    input: $('#email'),
                    error: $('#emailError'),
                    icon: $('#emailIcon'),
                    type: 'email'
                },
                {
                    input: $('#password'),
                    error: $('#passwordError'),
                    icon: $('#passwordIcon')
                },
                {
                    input: $('#password_confirmation'),
                    error: $('#passwordConfirmError'),
                    icon: $('#passwordConfirmIcon'),
                    type: 'confirm'
                }
            ];

            fields.forEach(field => {
                field.input.on('input focusout', function() {
                    validateField(field);
                });
            });

            $('#registerForm').submit(function(event) {
                event.preventDefault(); // Prevent default form submission
                
                let isValid = fields.every(field => validateField(field));
                if (!isValid) {
                    return;
                }

                // reCAPTCHA v3 integration
                grecaptcha.ready(function() {
                    grecaptcha.execute('6Lf8LBAqAAAAAICDNaJ-MzBoKGTxG8dLND9Bfeaq', { action: 'submit' }).then(function(token) {
                        $('#recaptchaResponse').val(token);
                        $('#registerForm')[0].submit();
                    });
                });
            });

            function validateField({
                input,
                error,
                icon,
                type
            }) {
                const value = input.val().trim();
                let isValid = true;
                let errorMessage = '';

                if (value === '') {
                    isValid = false;
                    errorMessage = '{{ __('messages.field_required') }}';
                } else if (type === 'email' && !isValidEmail(value)) {
                    isValid = false;
                    errorMessage = '{{ __('messages.invalid_email') }}';
                } else if (type === 'confirm' && value !== $('#password').val().trim()) {
                    isValid = false;
                    errorMessage = '{{ __('messages.password_mismatch') }}';
                }

                updateFieldStatus(input, error, icon, isValid, errorMessage);
                return isValid;
            }

            function updateFieldStatus(input, error, icon, isValid, errorMessage) {
                if (isValid) {
                    input.removeClass('border-red-500').addClass('border-green-500');
                    error.text('').removeClass('show');
                    icon.removeClass('icon-invalid').addClass('icon-valid');
                } else {
                    input.removeClass('border-green-500').addClass('border-red-500');
                    error.text(errorMessage).addClass('show');
                    icon.removeClass('icon-valid').addClass('icon-invalid');
                }
            }

            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }
        });
    </script>
    
</body>

</html>
