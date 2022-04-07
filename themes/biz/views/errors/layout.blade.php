<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Error Page')</title>

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #2d3436;
        }

        .content {
            height: 100vh;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            color: #fff;
            text-align: center;
        }

        h1 {
            font-size: 10em;
        }

        .message{
            font-size: 2.5em;
            font-weight: bold;
        }

        .sub-message{
            font-size: 1.2em;
            color: #b2bec3;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="container">
            @yield('content')
        </div>
    </div>
</body>

</html>