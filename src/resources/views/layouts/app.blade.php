<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Space Management')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        a.button, button {
            display: inline-block;
            padding: 8px 15px;
            margin-bottom: 10px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        a.button:hover, button:hover {
            background-color: #218838;
        }
        form input, form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .messages {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            background: #fafafa;
        }
        .msg {
            margin: 8px 0;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 70%;
            clear: both;
        }
        .msg.user {
            background: #007bff;
            color: white;
            float: right;
        }
        .msg.ai {
            background: #e1e1e1;
            float: left;
        }
    </style>
    @yield('head')
</head>
<body>
    <div class="container">
        <h1>@yield('header', 'Space Management System')</h1>
        @yield('content')
    </div>
    @yield('scripts')
</body>
</html>
