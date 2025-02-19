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
                                            <h5 class="m-b-10">Admin-Classroom-Page</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="admin-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Classrooms-Labs</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <!-- [ add-classroom ] start -->
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add/Edit Room <i class="feather icon-plus-circle"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="../../controllers/add-classrooms.php">
                                            <div class="form-group">
                                                <label for="room_number">Room Number</label>
                                                <input type="number" name="room_number" class="form-control" id="room_number" placeholder="Enter Room Number e.g. 101">
                                            </div>
                                            <div class="form-group">
                                                <label for="capacity">Capacity</label>
                                                <input type="number" min="1" max="50" name="capacity" class="form-control" id="capacity" placeholder="Enter capacity e.g. 20">
                                            </div>
                                            <div class="form-group">
                                                <label for="type">Room Type</label>
                                                <select name="type" class="form-control" id="type">
                                                    <option value="Room">Room</option>
                                                    <option value="Laboratory">Laboratory</option>

                                                </select>
                                            </div>

                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            <!-- [ add-classroom ] end -->
                            <!-- [ classroom-table ] start -->
                            <div class="col-sm-9">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Classrooms <i class="feather icon-sidebar"></i></h5>
                                        <input type="text" id="classroomSearch" class="form-control w-25" placeholder="Search classrooms...">
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="classroomTable">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>#</th>

                                                        <th>Room Number</th>
                                                        <th>Type</th>
                                                        <th>Capacity</th>

                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="classroomTableBody">
                                                    <!-- Dynamic rows will be appended here by JavaScript -->
                                                </tbody>
                                            </table>



                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ classroom-table ] end -->
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
        fetch('../../controllers/get-classrooms.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('classroomTableBody');
                data.forEach((classroom, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', classroom.id);
                    row.innerHTML = `
                                                                    <td>${index + 1}</td>
                                                
                                                                    <td>${classroom.room_number}</td>
                                                                    <td>${classroom.type}</td>
                                                                    <td>${classroom.capacity}</td>

                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning" onclick="editClassroom(${classroom.id})">Edit</button>
                                                                        <button class="btn btn-sm btn-danger" onclick="deleteClassroom(${classroom.id})">Delete</button>
                                                                    </td>
                                                                `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching classrooms:', error));
    });

    function deleteClassroom(id) {
        if (confirm('Are you sure you want to delete this classroom?')) {
            fetch(`../../controllers/delete-classroom.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Classroom deleted successfully', 'success');
                        document.querySelector(`#classroomTableBody tr[data-id="${id}"]`).remove();
                    } else {
                        showAlert('Failed to delete classroom', 'danger');
                    }
                })
                .catch(error => console.error('Error deleting classroom:', error));
        }
    }

    function editClassroom(id) {
        fetch(`../../controllers/edit-classroom.php?id=${id}`)
            .then(response => response.json())
            .then(classroom => {
                document.getElementById('room_number').value = classroom.room_number;
                document.getElementById('capacity').value = classroom.capacity;
                document.getElementById('type').value = classroom.type;
                document.querySelector('form').action = `../../controllers/edit-classroom.php?id=${id}`;
            })
            .catch(error => console.error('Error fetching classroom:', error));
    }
    // Search Functionality
    document.getElementById('classroomSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#classroomTable tbody tr');

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