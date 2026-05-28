<!DOCTYPE html>
<html>
<head>
    <title>Departments List</title>
</head>
<body>

    <h2>Departments</h2>
    
    <a href="{{ route('departments.create') }}">Add New Department</a>
    <br><br>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Department Name</th>
                <th>Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departments as $dept)
                <tr>
                    <td>{{ $dept->id }}</td>
                    <td>{{ $dept->department_name }}</td>
                    <td>{{ $dept->code }}</td>
                    <td>
                        <a href="{{ route('departments.edit', $dept->id) }}">Edit</a>
                        
                        <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No departments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>