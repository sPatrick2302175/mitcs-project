<!DOCTYPE html>
<html>
<head>
    <title>Create Department</title>
</head>
<body>

    <h2>Add New Department</h2>

    <form action="{{ route('departments.store') }}" method="POST">
        @csrf
        
        <label>Department Name:</label><br>
        <input type="text" name="department_name" required>
        <br><br>

        <label>Code:</label><br>
        <input type="text" name="code" required>
        <br><br>

        <button type="submit">Save</button>
        <a href="{{ route('departments.index') }}">Cancel</a>
    </form>

</body>
</html>