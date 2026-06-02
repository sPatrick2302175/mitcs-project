<!DOCTYPE html>
<html>
<head><title>Edit Division</title></head>
<body>
    <h2>Edit Division</h2>
    <form action="{{ route('divisions.update', $division->id) }}" method="POST">
        @csrf @method('PUT')
        
        <label>Division Name:</label><br>
        <input type="text" name="division_name" value="{{ $division->division_name }}" required><br><br>

        <label>Code:</label><br>
        <input type="text" name="code" value="{{ $division->code }}"><br><br>

        <label>Assign to Department:</label><br>
        <select name="department_id" required>
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ $division->department_id == $dept->id ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Update</button>
        <a href="{{ route('divisions.index') }}">Cancel</a>
    </form>
</body>
</html>