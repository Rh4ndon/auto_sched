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
                                            <h5 class="m-b-10">Admin-Teacher-Page</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="admin-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Teachers</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <!-- [ add-teacher ] start -->
                            <div class="col-sm-2">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add Teacher <i class="feather icon-plus-circle"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="../../controllers/add-teachers.php">
                                            <div class="form-group">
                                                <label for="name">Full Name</label>
                                                <input type="text" name="name" class="form-control" id="name" placeholder="Enter teacher full name">
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
                                                <input type="email" name="teacher_email" class="form-control" id="email" placeholder="Enter email">
                                            </div>

                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            <!-- [ add-teacher ] end -->
                            <!-- [ teacher-table ] start -->
                            <div class="col-sm-10">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Teachers <i class="feather icon-users"></i></h5>
                                        <input type="text" id="teacherSearch" class="form-control w-25" placeholder="Search teachers...">
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="teacherTable">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Gender</th>

                                                        <th>Subject Code</th>
                                                        <th>Subject Name</th>
                                                        <th>Semester</th>

                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="teacherTableBody">
                                                    <!-- Dynamic rows will be appended here by JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ teacher-table ] end -->
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
                    <h5 class="modal-title" id="enrollModalLabel">Assign Subject</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="hidden" id="enrollTeacherId">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="enrollSubjects">Subjects (Press CTRL then click to select multiple subjects)</label>
                        <select id="enrollSubjects" class="form-control" multiple required>
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
                        <label for="enrollAcademicYear">AcademicYear</label>
                        <input type="text" name="academic_year" class="form-control" id="academic_year" placeholder="Enter Academic Year (e.g. 2024-2025)" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
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
    });;
    // Fetch all teachers
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-teachers.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('teacherTableBody');
                data.forEach((teacher, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', teacher.id);
                    row.innerHTML = `
                                                                    <td>${index + 1}</td>
                                                                    <td>${teacher.name}</td>
                                                                    <td>${teacher.email}</td>
                                                                    <td>${teacher.gender}</td>
                                                                   
                                                                    <td>
                                                                        ${teacher.subjects.map(subject => subject.subject_code).join(',<br>')}
                                                                    </td>
                                                                    <td>
                                                                        ${teacher.subjects.map(subject => subject.subject_name).join(',<br>')}
                                                                    </td>
                                                                    <td>${teacher.semester}</td>
                                                                    

                                                                    <td>
                                                                        <button class="btn btn-sm btn-primary mb-2" onclick="enrollTeacher(${teacher.id})" ${teacher.semester === 'Not Yet Assigned' ? '' : 'style="display:none;"'}>Add Subject</button>
                                                                        <button class="btn btn-sm btn-warning mb-2" onclick="clearSubjects(${teacher.id})" ${teacher.semester === 'Not Yet Assigned' ? 'style="display:none;"' : ''}><i class="feather icon-trash-2"></i>Clear Subjects</button><br>
                                                                        <button class="btn btn-sm btn-info" onclick="editTeacher(${teacher.id})"><i class="feather icon-edit"></i></button>
                                                                        <button class="btn btn-sm btn-danger" onclick="deleteTeacher(${teacher.id})"><i class="feather icon-trash-2"></i></button>
                                                                    </td>
                                                                `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching teachers:', error));
    });

    function clearSubjects(id) {
        if (confirm('Are you sure you want to clear this teacher subjects?')) {
            fetch(`../../controllers/clear-subjects.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Teacher subjects cleared successfully', 'success');
                        const row = document.querySelector(`#teacherTableBody tr[data-id="${id}"]`);
                        row.querySelector('td:nth-child(4)').innerText = 'Not Yet Assigned';
                        row.querySelector('td:nth-child(5)').innerText = 'Not Yet Enrolled';
                        row.querySelector('td:nth-child(6)').innerText = 'Not Yet Enrolled';
                        row.querySelector('td:nth-child(7)').innerText = 'Not Yet Assigned';
                        row.querySelector('button:nth-child(1)').style.display = '';
                        row.querySelector('button:nth-child(2)').style.display = 'none';
                    } else {
                        showAlert('Failed to clear teacher subjects', 'danger');
                    }
                })
                .catch(error => console.error('Error clearing teacher subjects:', error));
        }
    }

    function deleteTeacher(id) {
        if (confirm('Are you sure you want to delete this teacher?')) {
            fetch(`../../controllers/delete-teacher.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Teacher deleted successfully', 'success');
                        document.querySelector(`#teacherTableBody tr[data-id="${id}"]`).remove();
                    } else {
                        showAlert('Failed to delete teacher', 'danger');
                    }
                })
                .catch(error => console.error('Error deleting teacher:', error));
        }
    }

    function editTeacher(id) {
        fetch(`../../controllers/edit-teacher.php?id=${id}`)
            .then(response => response.json())
            .then(teacher => {
                const nameParts = teacher.name.split(' ');
                document.getElementById('name').value = teacher.name;
                document.getElementById('gender').value = teacher.gender;
                document.getElementById('email').value = teacher.email;
                document.querySelector('form').action = `../../controllers/edit-teacher.php?id=${id}`;
            })
            .catch(error => console.error('Error fetching teacher:', error));
    }

    function enrollTeacher(id) {
        fetch(`../../controllers/get-teacher.php?id=${id}`)
            .then(response => response.json())
            .then(teacher => {
                document.getElementById('enrollModalLabel').innerText = `Assign Subject: ${teacher.name}`;
            })
            .catch(error => console.error('Error fetching teacher:', error));
        document.getElementById('enrollTeacherId').value = id;
        $('#enrollModal').modal('show');
    }

    document.getElementById('enrollForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const id = document.getElementById('enrollTeacherId').value;
        const semester = document.getElementById('enrollSemester').value;
        const academic_year = document.getElementById('academic_year').value;
        const subjects = Array.from(document.getElementById('enrollSubjects').selectedOptions).map(option => option.value);

        fetch(`../../controllers/assign-teacher.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    semester,
                    academic_year,
                    subjects
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Teacher enrolled successfully', 'success');
                    const row = document.querySelector(`#teacherTableBody tr[data-id="${id}"]`);

                    row.querySelector('td:nth-child(5)').innerText = data.subjects.map(subject => subject.subject_code).join(',\n');
                    row.querySelector('td:nth-child(6)').innerText = data.subjects.map(subject => subject.subject_name).join(',\n');
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
                    row.querySelector('td:nth-child(7)').innerText = semesterText;

                    row.querySelector('button:nth-child(1)').style.display = 'none';
                    row.querySelector('button:nth-child(2)').style.display = '';
                    $('#enrollModal').modal('hide');
                } else {
                    showAlert('Failed to enroll teacher', 'danger');
                    console.error(data.error);
                }
            })
            .catch(error => console.error('Error enrolling teacher:', error));
    });



    // Search Functionality
    document.getElementById('teacherSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#teacherTable tbody tr');

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