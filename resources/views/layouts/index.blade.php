<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ブログ</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>

<body>
    <nav>
        <li><a href="/">TOP（ブログ一覧）</a></li>

        @auth
            <li><a href="{{ route('mypage.posts') }}">マイブログ一覧</a></li>
            <li>
                <form method="post" action="{{ route('logout') }}">
                    @csrf<input type="submit" value="ログアウト">
                </form>
            </li>
        @else
            <li><a href="{{ route('login') }}">ログイン</a></li>
        @endauth
    </nav>
    @yield('content')
</body>

</html>
