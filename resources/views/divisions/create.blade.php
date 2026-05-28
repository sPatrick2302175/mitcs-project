<!DOCTYPE html>
<html>
<head><title>Create Division</title></head>
<body>
    <h2>Add New Division</h2>
    <form action="{{ route('divisions.store') }}" method="POST">
        @csrf
        <label>Division Name:</label><br>
        <input type="text" name="division_name" required><br><br>

        <label>Code:</label><br>
        <input type="text" name="code"><br><br>

        <button type="submit">Save</button>
        <a href="{{ route('divisions.index') }}">Cancel</a>
    </form>
</body>
</html>