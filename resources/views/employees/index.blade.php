<!DOCTYPE html>
<html>
<head><title>Employees List</title></head>
<body>
    <h2>Employees</h2>
    <a href="{{ route('employees.create') }}">Add New Employee</a>
    <a href="{{ route('dashboard') }}">Back to Dashboard</a>
    <br><br>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Emp ID Number</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Position</th>
                <th>Dept ID</th>
                <th>Div ID</th>
                <th>Leave Credits</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
                <tr>
                    <td>{{ $emp->employee_id_number }}</td>
                    <td>{{ $emp->first_name }}</td>
                    <td>{{ $emp->last_name }}</td>
                    <td>{{ $emp->position }}</td>
                    <td>{{ $emp->department_id }}</td>
                    <td>{{ $emp->division_id }}</td>
                    <td>{{ $emp->leave_credits }}</td>
                    <td>
                        <a href="{{ route('employees.edit', $emp->id) }}">Edit</a>
                        <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this employee?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No employees found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>