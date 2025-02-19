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
                                            <h5 class="m-b-10">Admin-Subject-Page</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="admin-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Subjects</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <!-- [ add-subject ] start -->
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add/Edit Subject <i class="feather icon-plus-circle"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="../../controllers/add-subjects.php">
                                            <div class="form-group">
                                                <label for="subject_code">Subject Code</label>
                                                <input type="text" name="subject_code" class="form-control" id="subject_code" placeholder="Enter subject code">
                                            </div>
                                            <div class="form-group">
                                                <label for="subject_name">Subject Name</label>
                                                <input type="text" name="subject_name" class="form-control" id="subject_name" placeholder="Enter subject name">
                                            </div>
                                            <div class="form-group">
                                                <label for="semester">Semester</label>
                                                <select name="semester" class="form-control" id="semester">
                                                    <option value="1">1st</option>
                                                    <option value="2">2nd</option>
                                                    <option value="midyear">Midyear</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="year_level">Year Level</label>
                                                <input type="number" min="1" max="4" name="year_level" class="form-control" id="year_level" placeholder="Enter year level">
                                            </div>

                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            <!-- [ add-subject ] end -->
                            <!-- [ subject-table ] start -->
                            <div class="col-sm-9">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Subjects <i class="feather icon-folder"></i></h5>
                                        <input type="text" id="subjectSearch" class="form-control w-25" placeholder="Search subjects...">
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="subjectTable">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Subject Code</th>
                                                        <th>Subject Name</th>
                                                        <th>Semester</th>
                                                        <th>Year Level</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="subjectTableBody">
                                                    <!-- Dynamic rows will be appended here by JavaScript -->
                                                </tbody>
                                            </table>



                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ subject-table ] end -->
                        </div>
                        <!-- [ Main Content ] end -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->


<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-subjects.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('subjectTableBody');
                data.forEach((subject, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', subject.id);
                    row.innerHTML = `
                                                                    <td>${index + 1}</td>
                                                                    <td>${subject.subject_code}</td>
                                                                    <td>${subject.subject_name}</td>
                                                                    <td>${subject.semester}</td>
                                                                    <td>${subject.year_level}</td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning" onclick="editSubject(${subject.id})">Edit</button>
                                                                        <button class="btn btn-sm btn-danger" onclick="deleteSubject(${subject.id})">Delete</button>
                                                                    </td>
                                                                `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching subjects:', error));
    });

    function deleteSubject(id) {
        if (confirm('Are you sure you want to delete this subject?')) {
            fetch(`../../controllers/delete-subject.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Subject deleted successfully', 'success');
                        document.querySelector(`#subjectTableBody tr[data-id="${id}"]`).remove();
                    } else {
                        showAlert('Failed to delete subject', 'danger');
                    }
                })
                .catch(error => console.error('Error deleting subject:', error));
        }
    }

    function editSubject(id) {
        fetch(`../../controllers/edit-subject.php?id=${id}`)
            .then(response => response.json())
            .then(subject => {
                document.getElementById('subject_code').value = subject.subject_code;
                document.getElementById('subject_name').value = subject.subject_name;
                document.getElementById('semester').value = subject.semester;
                document.getElementById('year_level').value = subject.year_level;
                document.querySelector('form').action = `../../controllers/edit-subject.php?id=${id}`;
            })
            .catch(error => console.error('Error fetching subject:', error));
    }
    // Search Functionality
    document.getElementById('subjectSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#subjectTable tbody tr');

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