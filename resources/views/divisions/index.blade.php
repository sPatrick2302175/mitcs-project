<!DOCTYPE html>
<html>
<head><title>Divisions List</title></head>
<body>
    <h2>Divisions</h2>
    <a href="{{ route('divisions.create') }}">Add New Division</a>
    <br><br>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Division Name</th>
                <th>Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($divisions as $div)
                <tr>
                    <td>{{ $div->id }}</td>
                    <td>{{ $div->division_name }}</td>
                    <td>{{ $div->code }}</td>
                    <td>
                        <a href="{{ route('divisions.edit', $div->id) }}">Edit</a>
                        <form action="{{ route('divisions.destroy', $div->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No divisions found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>