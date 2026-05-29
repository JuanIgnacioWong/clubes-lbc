<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="margin:0 0 16px 0;">{{ $title }}</h2>

    @foreach($lines as $line)
        <p style="margin:0 0 10px 0;">{{ $line }}</p>
    @endforeach

    @if(!empty($actionUrl) && !empty($actionText))
        <p style="margin-top:18px;">
            <a href="{{ $actionUrl }}" style="display:inline-block;background:#145FB0;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">{{ $actionText }}</a>
        </p>
    @endif

    <p style="margin-top:24px;color:#475569;font-size:12px;">Plataforma de clubes LBC Chile</p>
</body>
</html>
