<?php @include 'header.php'; ?>

<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <!-- [ breadcrumb ] start -->
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-12">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Admin-Student-Page</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="admin-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Students</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <!-- [ add-student ] start -->
                            <div class="col-sm-2">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add Student <i class="feather icon-plus-circle"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="../../controllers/add-students.php">
                                            <div class="form-group">
                                                <label for="name">Full Name</label>
                                                <input type="text" name="name" class="form-control" id="name" placeholder="Enter student full name">
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Gender</label>
                                                <select type="text" name="gender" class="form-control" id="gender" placeholder="Enter Gender">
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" name="student_email" class="form-control" id="email" placeholder="Enter email">
                                            </div>

                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            <!-- [ add-student ] end -->
                            <!-- [ student-table ] start -->
                            <div class="col-sm-10">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Students <i class="feather icon-users"></i></h5>
                                        <input type="text" id="studentSearch" class="form-control w-25" placeholder="Search students...">
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="studentTable">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Gender</th>
                                                        <th>Course-Section</th>
                                                        <th>Subject Code</th>
                                                        <th>Subject Name</th>
                                                        <th>Semester</th>
                                                        <th>Year Level</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="studentTableBody">
                                                    <!-- Dynamic rows will be appended here by JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ student-table ] end -->
                        </div>
                        <!-- [ Main Content ] end -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->

<!-- Enroll Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" role="dialog" aria-labelledby="enrollModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="enrollForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollModalLabel">Enroll Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="hidden" id="enrollStudentId">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="enrollSection">Section</label>
                        <select id="enrollSection" class="form-control" required>
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="enrollSemester">Semester</label>
                        <select id="enrollSemester" class="form-control" required>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="midyear">Midyear</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="enrollYear">Year Level</label>
                        <select id="enrollYear" class="form-control" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="enrollAcademicYear">AcademicYear</label>
                        <input type="text" name="academic_year" class="form-control" id="academic_year" placeholder="Enter Academic Year (e.g. 2024-2025)" required>
                    </div>
                    <div class="form-group">
                        <label for="enrollSubjects">Subjects (Press CTRL then click to select multiple subjects)</label>
                        <select id="enrollSubjects" class="form-control" multiple required>
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enroll</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    // Fetch all subjects
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-subjects.php')
            .then(response => response.json())
            .then(data => {
                const enrollSubjects = document.getElementById('enrollSubjects');
                data.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.id;
                    option.innerText = `${subject.subject_code} (${subject.subject_name})`;
                    enrollSubjects.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching subjects:', error));
    });
    // Fetch all sections
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-sections.php')
            .then(response => response.json())
            .then(data => {
                const enrollSection = document.getElementById('enrollSection');
                data.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.innerText = `${section.section_name} (${section.year_level}st Year)`;
                    enrollSection.appendChild(option);
                });
                // Store sections for later use
                window.sections = data.reduce((acc, section) => {
                    acc[section.id] = section.section_name;
                    return acc;
                }, {});
            })
            .catch(error => console.error('Error fetching sections:', error));
    });
    // Fetch all students
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-students.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('studentTableBody');
                data.forEach((student, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', student.id);
                    row.innerHTML = `
                                                                    <td>${index + 1}</td>
                                                                    <td>${student.name}</td>
                                                                    <td>${student.email}</td>
                                                                    <td>${student.gender}</td>
                                                                    <td>${student.section}</td>
                                                                    <td>
                                                                        ${student.subjects.map(subject => subject.subject_code).join(',<br>')}
                                                                    </td>
                                                                    <td>
                                                                        ${student.subjects.map(subject => subject.subject_name).join(',<br>')}
                                                                    </td>
                                                                    <td>${student.semester}</td>
                                                                    <td>${student.year_level}</td>

                                                                    <td>
                                                                        <button class="btn btn-sm btn-primary mb-2" onclick="enrollStudent(${student.id})" ${student.section === 'Not Yet Enrolled' ? '' : 'style="display:none;"'}>Enroll</button>
                                                                        <button class="btn btn-sm btn-warning mb-2" onclick="clearEnrollment(${student.id})" ${student.section === 'Not Yet Enrolled' ? 'style="display:none;"' : ''}><i class="feather icon-trash-2"></i>Clear Enroll</button><br>
                                                                        <button class="btn btn-sm btn-info" onclick="editStudent(${student.id})"><i class="feather icon-edit"></i></button>
                                                                        <button class="btn btn-sm btn-danger" onclick="deleteStudent(${student.id})"><i class="feather icon-trash-2"></i></button>
                                                                    </td>
                                                                `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching students:', error));
    });

    function clearEnrollment(id) {
        if (confirm('Are you sure you want to clear this student enrollment?')) {
            fetch(`../../controllers/clear-enrollment.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Student enrollment cleared successfully', 'success');
                        const row = document.querySelector(`#studentTableBody tr[data-id="${id}"]`);
                        row.querySelector('td:nth-child(5)').innerText = 'Not Yet Enrolled';
                        row.querySelector('td:nth-child(6)').innerText = 'Not Yet Enrolled';
                        row.querySelector('td:nth-child(7)').innerText = 'Not Yet Enrolled';
                        row.querySelector('td:nth-child(8)').innerText = 'Not Yet Enrolled';
                        row.querySelector('td:nth-child(9)').innerText = 'Not Yet Enrolled';
                        row.querySelector('button:nth-child(1)').style.display = '';
                        row.querySelector('button:nth-child(2)').style.display = 'none';
                    } else {
                        showAlert('Failed to clear student enrollment', 'danger');
                    }
                })
                .catch(error => console.error('Error clearing student enrollment:', error));
        }
    }

    function deleteStudent(id) {
        if (confirm('Are you sure you want to delete this student?')) {
            fetch(`../../controllers/delete-student.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Student deleted successfully', 'success');
                        document.querySelector(`#studentTableBody tr[data-id="${id}"]`).remove();
                    } else {
                        showAlert('Failed to delete student', 'danger');
                    }
                })
                .catch(error => console.error('Error deleting student:', error));
        }
    }

    function editStudent(id) {
        fetch(`../../controllers/edit-student.php?id=${id}`)
            .then(response => response.json())
            .then(student => {
                const nameParts = student.name.split(' ');
                document.getElementById('name').value = student.name;
                document.getElementById('gender').value = student.gender;
                document.getElementById('email').value = student.email;
                document.querySelector('form').action = `../../controllers/edit-student.php?id=${id}`;
            })
            .catch(error => console.error('Error fetching student:', error));
    }

    function enrollStudent(id) {
        fetch(`../../controllers/get-student.php?id=${id}`)
            .then(response => response.json())
            .then(student => {
                document.getElementById('enrollModalLabel').innerText = `Enroll Student: ${student.name}`;
            })
            .catch(error => console.error('Error fetching student:', error));
        document.getElementById('enrollStudentId').value = id;
        $('#enrollModal').modal('show');
    }

    document.getElementById('enrollForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const id = document.getElementById('enrollStudentId').value;
        const section = document.getElementById('enrollSection').value;
        const semester = document.getElementById('enrollSemester').value;
        const year = document.getElementById('enrollYear').value;
        const academic_year = document.getElementById('academic_year').value;
        const subjects = Array.from(document.getElementById('enrollSubjects').selectedOptions).map(option => option.value);

        fetch(`../../controllers/enroll-student.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    section,
                    semester,
                    year,
                    academic_year,
                    subjects
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Student enrolled successfully', 'success');
                    const row = document.querySelector(`#studentTableBody tr[data-id="${id}"]`);
                    row.querySelector('td:nth-child(5)').innerText = window.sections[section] || section;
                    row.querySelector('td:nth-child(6)').innerText = data.subjects.map(subject => subject.subject_code).join(',\n');
                    row.querySelector('td:nth-child(7)').innerText = data.subjects.map(subject => subject.subject_name).join(',\n');
                    let semesterText = '';
                    switch (semester) {
                        case '1':
                            semesterText = '1st Semester';
                            break;
                        case '2':
                            semesterText = '2nd Semester';
                            break;
                        case 'midyear':
                            semesterText = 'Midyear';
                            break;
                        default:
                            semesterText = semester + ' Semester';
                    }
                    row.querySelector('td:nth-child(8)').innerText = semesterText;
                    let yearText = '';
                    switch (year) {
                        case '1':
                            yearText = '1st Year';
                            break;
                        case '2':
                            yearText = '2nd Year';
                            break;
                        case '3':
                            yearText = '3rd Year';
                            break;
                        case '4':
                            yearText = '4th Year';
                            break;
                        default:
                            yearText = year + ' Year';
                    }
                    row.querySelector('td:nth-child(9)').innerText = yearText;
                    row.querySelector('button:nth-child(1)').style.display = 'none';
                    row.querySelector('button:nth-child(2)').style.display = '';
                    $('#enrollModal').modal('hide');
                } else {
                    showAlert('Failed to enroll student', 'danger');
                    console.error(data.error);
                }
            })
            .catch(error => console.error('Error enrolling student:', error));
    });



    // Search Functionality
    document.getElementById('studentSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#studentTable tbody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function showAlert(message, type = 'success') {
        // Remove existing alert if any
        let existingAlert = document.querySelector('.floating-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        // Create the alert element
        let alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show floating-alert`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
										<strong>Success!</strong> ${message}
										<button type="button" class="close" data-dismiss="alert" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									`;

        // Append to body
        document.body.appendChild(alertDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
</script>
<?php
if (isset($_GET['msg'])) {
    echo "<script>showAlert('{$_GET['msg']}')</script>";
}
?>
<?php @include 'footer.php'; ?>