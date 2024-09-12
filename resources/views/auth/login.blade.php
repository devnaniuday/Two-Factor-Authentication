<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
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
            transition: border-color 0.3s ease-in-out;
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

        .icon::before {
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
        }

        .icon-valid::before {
            content: '\f00c';
            color: #38a169;
        }
        
        .icon-valid {
            content: '\f00c';
            color: #38a169;
            transform: unset;
        }

        .icon-invalid::before {
            content: '\f00d';
            color: #e53e3e;
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
        <h2 class="text-2xl font-bold text-center mb-4">{{ __('messages.login_form') }}</h2>
        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">{{ __('messages.email') }}</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" autofocus>
                <span id="emailError" class="error-message"></span>
                <span id="emailIcon" class="icon"></span>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">{{ __('messages.password') }}</label>
                <input type="password" id="password" name="password" class="form-input">
                <span id="passwordError" class="error-message"></span>
                <span id="passwordIcon" class="icon"></span>
            </div>
            {{-- {!! HCaptcha::display() !!}
            {!! HCaptcha::script() !!} --}}
            @if ($errors->has('login_error'))
                <span class="error-message">{{ $errors->first('login_error') }}</span>
            @endif
            <button type="submit" class="submit-btn">{{ __('messages.login') }}</button>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            const fields = [
                { input: $('#email'), error: $('#emailError'), icon: $('#emailIcon') },
                { input: $('#password'), error: $('#passwordError'), icon: $('#passwordIcon') }
            ];

            fields.forEach(field => {
                field.input.on('input focusout', function () {
                    validateField(field);
                });
            });

            $('#loginForm').submit(function (event) {
                let isValid = fields.every(field => validateField(field));
                if (!isValid) {
                    event.preventDefault();
                }
            });

            function validateField({ input, error, icon }) {
                const value = input.val().trim();
                let isValid = true;
                let errorMessage = '';

                if (value === '') {
                    isValid = false;
                    errorMessage = '{{ __('messages.validation_failed') }}';
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
        });
    </script>
</body>
