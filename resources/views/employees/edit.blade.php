<!DOCTYPE html>
<html>
<head><title>Edit Employee</title></head>
<body>
    <h2>Edit Employee</h2>
    
    @if ($errors->any())
        <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px;">
            <strong>Whoops! Something went wrong:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
        <select name="department_id" id="department_dropdown" required>
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ $employee->department_id == $dept->id ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
            @endforeach
        </select><br><br>

        <label>Division:</label><br>
        <select name="division_id" id="division_dropdown" required>
            <option value="">-- Select Division --</option>
            @foreach($divisions as $div)
                <option value="{{ $div->id }}" data-department="{{ $div->department_id }}" {{ $employee->division_id == $div->id ? 'selected' : '' }}>
                    {{ $div->division_name }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Update Employee</button>
        <a href="{{ route('employees.index') }}">Cancel</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deptDropdown = document.getElementById('department_dropdown');
            const divDropdown = document.getElementById('division_dropdown');
            
            // Save a copy of all division options when the page loads
            const allDivOptions = Array.from(divDropdown.options).filter(opt => opt.value !== "");

            function filterDivisions() {
                const selectedDept = deptDropdown.value;

                // Reset the division dropdown
                divDropdown.innerHTML = '<option value="">-- Select Division --</option>';
                
                if (!selectedDept) {
                    divDropdown.innerHTML = '<option value="">-- Select Department First --</option>';
                    divDropdown.disabled = true;
                    return;
                }

                divDropdown.disabled = false;
                let hasMatches = false;

                // Only add options that match the selected department
                allDivOptions.forEach(option => {
                    if (option.getAttribute('data-department') === selectedDept) {
                        divDropdown.appendChild(option.cloneNode(true));
                        hasMatches = true;
                    }
                });

                if (!hasMatches) {
                    divDropdown.innerHTML = '<option value="">-- No Divisions in this Dept --</option>';
                    divDropdown.disabled = true;
                }
            }

            // Run this immediately on page load so it filters based on the employee's current department
            filterDivisions();

            // Run it again anytime the user changes the department
            deptDropdown.addEventListener('change', filterDivisions);
        });
    </script>
</body>
</html>