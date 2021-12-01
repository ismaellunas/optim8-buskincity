<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
</head>
<body>
    <h1>Test Translation</h1>
    <h3>Auth</h3>
    <p>
        <ul>
            <li>Password: @lang('auth.password')</li>
            <li>Failed: @lang('auth.failed')</li>
            <li>Throttle: @lang('auth.throttle')</li>
        </ul>
    </p>

    <h3>Passwords</h3>
    <p>
        <ul>
            <li>Reset: @lang('passwords.reset')</li>
            <li>Sent: @lang('passwords.sent')</li>
            <li>User: @lang('passwords.user')</li>
        </ul>
    </p>
    <hr>
    <p>
        <b>Note</b><br>
        Change on url using prefix locale if you want to check another language.<br>
        Example: http://localhost:8000/de/test/translation (for check translation on Germany)
    </p>
</body>
</html>