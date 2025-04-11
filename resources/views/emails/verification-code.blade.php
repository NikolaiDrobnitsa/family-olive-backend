<!-- resources/views/emails/verification-code.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Код подтверждения</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .content {
            padding: 20px 0;
        }
        .verification-code {
            font-size: 28px;
            text-align: center;
            letter-spacing: 5px;
            margin: 30px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .footer {
            padding: 20px 0;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Family Olive Club</h1>
    </div>

    <div class="content">
        <h2>Подтверждение адреса электронной почты</h2>
        <p>Здравствуйте!</p>
        <p>Для входа на сайт Family Olive Club используйте следующий код подтверждения:</p>

        <div class="verification-code">
            {{ $code }}
        </div>

        <p>Код действителен в течение 30 минут.</p>
        <p>Если вы не запрашивали этот код, можете проигнорировать это письмо.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Family Olive Club. Все права защищены.</p>
        <p>Это автоматическое сообщение, пожалуйста, не отвечайте на него.</p>
    </div>
</div>
</body>
</html>

<!-- resources/views/auth/custom-login.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Family Olive Club - Авторизация</title>

    <!-- Стили -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .verification-code-container {
            display: none;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 200px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="login-container">
                <div class="logo">
                    <img src="/images/logo.png" alt="Family Olive Club">
                </div>

                <div id="login-form">
                    <h3 class="text-center mb-4">Добро пожаловать в Family Olive Club</h3>
                    <p class="text-center mb-4">Пожалуйста, войдите для доступа к сайту</p>

                    <form id="auth-form">
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                            <div class="invalid-feedback" id="phone-error"></div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Войти</button>
                        </div>
                    </form>
                </div>

                <div id="verification-form" class="verification-code-container">
                    <h3 class="text-center mb-4">Подтверждение Email</h3>
                    <p class="text-center mb-4">Мы отправили код подтверждения на ваш email. Пожалуйста, введите его ниже:</p>

                    <form id="verify-form">
                        <input type="hidden" id="verify-email" name="email">

                        <div class="mb-3">
                            <label for="verification-code" class="form-label">Код подтверждения</label>
                            <input type="text" class="form-control" id="verification-code" name="code" maxlength="6" required>
                            <div class="invalid-feedback" id="code-error"></div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Подтвердить</button>
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-link" id="resend-code">Отправить код повторно</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Скрипты -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Обработка отправки формы авторизации
        $('#auth-form').on('submit', function(e) {
            e.preventDefault();

            // Получение данных из формы
            const formData = {
                name: $('#name').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Добавление UTM-меток, если они есть в URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('utm_source')) formData.utm_source = urlParams.get('utm_source');
            if (urlParams.has('utm_medium')) formData.utm_medium = urlParams.get('utm_medium');
            if (urlParams.has('utm_campaign')) formData.utm_campaign = urlParams.get('utm_campaign');
            if (urlParams.has('utm_term')) formData.utm_term = urlParams.get('utm_term');
            if (urlParams.has('utm_content')) formData.utm_content = urlParams.get('utm_content');

            // Отправка запроса
            $.ajax({
                type: 'POST',
                url: '/auth/login',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        if (response.verified) {
                            // Если пользователь уже верифицирован, переходим на главную страницу
                            window.location.href = '/';
                        } else {
                            // Если требуется верификация, показываем форму для ввода кода
                            $('#login-form').hide();
                            $('#verification-form').show();
                            $('#verify-email').val(response.email);
                        }
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;

                    // Очистка предыдущих ошибок
                    $('.invalid-feedback').text('');
                    $('.form-control').removeClass('is-invalid');

                    // Отображение ошибок
                    if (errors) {
                        for (const field in errors) {
                            $(`#${field}`).addClass('is-invalid');
                            $(`#${field}-error`).text(errors[field][0]);
                        }
                    }
                }
            });
        });

        // Обработка отправки формы верификации
        $('#verify-form').on('submit', function(e) {
            e.preventDefault();

            // Получение данных из формы
            const formData = {
                email: $('#verify-email').val(),
                code: $('#verification-code').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Отправка запроса
            $.ajax({
                type: 'POST',
                url: '/auth/verify',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Переход на главную страницу после успешной верификации
                        window.location.href = '/';
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;

                    // Очистка предыдущих ошибок
                    $('#code-error').text('');
                    $('#verification-code').removeClass('is-invalid');

                    // Отображение ошибок
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        $('#verification-code').addClass('is-invalid');
                        $('#code-error').text(xhr.responseJSON.message);
                    } else if (errors && errors.code) {
                        $('#verification-code').addClass('is-invalid');
                        $('#code-error').text(errors.code[0]);
                    }
                }
            });
        });

        // Повторная отправка кода
        $('#resend-code').on('click', function() {
            const email = $('#verify-email').val();

            $.ajax({
                type: 'POST',
                url: '/auth/resend-code',
                data: {
                    email: email,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert('Код успешно отправлен повторно на ваш email.');
                    }
                },
                error: function(xhr) {
                    alert('Ошибка при отправке кода. Пожалуйста, попробуйте позже.');
                }
            });
        });
    });
</script>
</body>
</html>
