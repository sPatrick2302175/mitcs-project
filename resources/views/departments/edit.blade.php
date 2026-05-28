<!DOCTYPE html>
<html>
<head>
    <title>Edit Department</title>
</head>
<body>

    <h2>Edit Department</h2>

    <form action="{{ route('departments.update', $department->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <label>Department Name:</label><br>
        <input type="text" name="department_name" value="{{ $department->department_name }}" required>
        <br><br>

        <label>Code:</label><br>
        <input type="text" name="code" value="{{ $department->code }}" required>
        <br><br>

        <button type="submit">Update</button>
        <a href="{{ route('departments.index') }}">Cancel</a>
    </form>

</body>
</html>