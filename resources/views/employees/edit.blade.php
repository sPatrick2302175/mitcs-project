<!DOCTYPE html>
<html>
<head><title>Edit Employee</title></head>
<body>
    <h2>Edit Employee</h2>
    <form action="{{ route('employees.update', $employee->id) }}" method="POST">
        @csrf @method('PUT')
        
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="{{ $employee->first_name }}" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="{{ $employee->last_name }}" required><br><br>

        <label>Position:</label><br>
        <input type="text" name="position" value="{{ $employee->position }}" required><br><br>

        <label>Leave Credits:</label><br>
        <input type="number" name="leave_credits" value="{{ $employee->leave_credits }}" required><br><br>

        <label>Department:</label><br>
        <select name="department_id" required>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ $employee->department_id == $dept->id ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select><br><br>

        <label>Division:</label><br>
        <select name="division_id" required>
            @foreach($divisions as $div)
                <option value="{{ $div->id }}" {{ $employee->division_id == $div->id ? 'selected' : '' }}>
                    {{ $div->division_name }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Update Employee</button>
        <a href="{{ route('employees.index') }}">Cancel</a>
    </form>
</body>
</html>