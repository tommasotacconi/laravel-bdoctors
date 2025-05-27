<!DOCTYPE html>
<html>
<head>
    <title>Placeholder Images</title>
</head>
<body>
    @foreach ($images as $ind => $img)
        <p><a href="{{ $img['urls']['small'] }}">img #{{ $ind + 1 }}</a></p>
        <img src="{{ $img['urls']['small'] }}" alt="Image #{{ $ind + 1 }}">
    @endforeach
</body>
</html>
