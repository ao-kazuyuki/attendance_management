<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-item__group">
            <img class="header-logo__img" src="{{ asset('img/logo.svg') }}" alt="ロゴ画像">
            @yield('header-menu')
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>