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

        <label>Assign to Department:</label><br>
        <select name="department_id" required>
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
            @endforeach
        </select><br><br>

        <button type="submit">Save</button>
        <a href="{{ route('divisions.index') }}">Cancel</a>
    </form>
</body>
</html>