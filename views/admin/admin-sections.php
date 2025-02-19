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
                                            <h5 class="m-b-10">Admin-Course-Section-Page</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="admin-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Sections</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <!-- [ add-section ] start -->
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add/Edit Section <i class="feather icon-plus-circle"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="../../controllers/add-sections.php">
                                            <div class="form-group">
                                                <label for="department_code">Course Code</label>
                                                <input type="text" name="department_code" class="form-control" id="department_code" placeholder="Enter department code e.g. BSIT">
                                            </div>
                                            <div class="form-group">
                                                <label for="section_code">Section Code</label>
                                                <input type="text" name="section_code" class="form-control" id="section_code" placeholder="Enter section code e.g. 1A">
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
                                            <div class="form-group">
                                                <label for="academic_year">Academic Year</label>
                                                <input type="text" name="academic_year" class="form-control" id="academic_year" placeholder="Enter academic year e.g. 2024-2025">
                                            </div>

                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            <!-- [ add-section ] end -->
                            <!-- [ section-table ] start -->
                            <div class="col-sm-9">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Course and Sections <i class="feather icon-folder"></i></h5>
                                        <input type="text" id="sectionSearch" class="form-control w-25" placeholder="Search sections...">
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="sectionTable">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>#</th>

                                                        <th>Section Name</th>
                                                        <th>Semester</th>
                                                        <th>Year Level</th>
                                                        <th>Academic Year</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sectionTableBody">
                                                    <!-- Dynamic rows will be appended here by JavaScript -->
                                                </tbody>
                                            </table>



                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ section-table ] end -->
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
        fetch('../../controllers/get-sections.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('sectionTableBody');
                data.forEach((section, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', section.id);
                    row.innerHTML = `
                                                                    <td>${index + 1}</td>
                                                
                                                                    <td>${section.section_name}</td>
                                                                    <td>${section.semester}</td>
                                                                    <td>${section.year_level}</td>
                                                                    <td>${section.academic_year}</td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning" onclick="editSection(${section.id})">Edit</button>
                                                                        <button class="btn btn-sm btn-danger" onclick="deleteSection(${section.id})">Delete</button>
                                                                    </td>
                                                                `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching sections:', error));
    });

    function deleteSection(id) {
        if (confirm('Are you sure you want to delete this section?')) {
            fetch(`../../controllers/delete-section.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Section deleted successfully', 'success');
                        document.querySelector(`#sectionTableBody tr[data-id="${id}"]`).remove();
                    } else {
                        showAlert('Failed to delete section', 'danger');
                    }
                })
                .catch(error => console.error('Error deleting section:', error));
        }
    }

    function editSection(id) {
        fetch(`../../controllers/edit-section.php?id=${id}`)
            .then(response => response.json())
            .then(section => {
                document.getElementById('department_code').value = section.section_name.split('-')[0];
                document.getElementById('section_code').value = section.section_name.split('-')[1];
                document.getElementById('semester').value = section.semester;
                document.getElementById('year_level').value = section.year_level;
                document.getElementById('academic_year').value = section.academic_year;
                document.querySelector('form').action = `../../controllers/edit-section.php?id=${id}`;
            })
            .catch(error => console.error('Error fetching section:', error));
    }
    // Search Functionality
    document.getElementById('sectionSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#sectionTable tbody tr');

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