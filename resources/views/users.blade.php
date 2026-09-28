<!-- your.view.name.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Names View</title>
</head>
<body>
    <h1>List of Names</h1>
    <ul>
        @foreach($names as $name)
            <li>{{ $name }}</li>
        @endforeach
    </ul>
</body>
</html>