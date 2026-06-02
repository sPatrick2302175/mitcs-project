<!DOCTYPE html>
<html>
<head><title>Create Employee</title></head>
<body>
    <h2>Add New Employee</h2>

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

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        
        <label>Employee ID Number:</label><br>
        <input type="text" name="employee_id_number" required><br><br>

        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br><br>

        <label>Position:</label><br>
        <input type="text" name="position" required><br><br>

        <label>Leave Credits:</label><br>
        <input type="number" name="leave_credits"><br><br>

        <label>Department:</label><br>
        <select name="department_id" id="department_dropdown" required>
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
            @endforeach
        </select><br><br>

        <label>Division:</label><br>
        <select name="division_id" id="division_dropdown" required disabled>
            <option value="">-- Select Department First --</option>
            @foreach($divisions as $div)
                <option value="{{ $div->id }}" data-department="{{ $div->department_id }}">
                    {{ $div->division_name }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Save Employee</button>
        <a href="{{ route('employees.index') }}">Cancel</a>
    </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deptDropdown = document.getElementById('department_dropdown');
            const divDropdown = document.getElementById('division_dropdown');
            
            // Save a copy of all division options when the page loads
            const allDivOptions = Array.from(divDropdown.options).filter(opt => opt.value !== "");

            deptDropdown.addEventListener('change', function() {
                const selectedDept = this.value;

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
            });
        });
    </script>
</body>
</html>