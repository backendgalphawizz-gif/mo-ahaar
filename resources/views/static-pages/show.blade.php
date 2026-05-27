<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->title }}</title>
    @include('layouts.favicon-dynamic')
    <style>
        body { margin: 0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f7f9fc; color: #1e2a38; }
        .wrap { max-width: 900px; margin: 40px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #e8edf3; border-radius: 12px; overflow: hidden; }
        .head { padding: 18px 22px; border-bottom: 1px solid #edf2f7; background: linear-gradient(135deg, #0da487 0%, #0a7b67 100%); color: #fff; }
        .body { padding: 22px; line-height: 1.7; }
        .body h1, .body h2, .body h3 { margin-top: 0; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <h2 style="margin:0;">{{ $page->title }}</h2>
            </div>
            <div class="body">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</body>
</html>
