<?php @include 'header.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Custom styles for the schedule -->
<style>
    .lunch-break {
        background-color: #ffcccb;
        /* Light red for lunch break */
        font-style: italic;
        /* Italicize lunch break text */
    }

    .online-class {
        background-color: #add8e6;
        /* Light blue for online class */
        font-weight: bold;
        /* Make "Online Class" stand out */
    }

    .lab {
        font-style: italic;
        /* Italicize lab classes */
    }

    .pe {
        font-weight: bold;
        /* Bold PE classes */
    }
</style>
<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <!-- [ breadcrumb ] start -->
                        <div class="page-header dont-print">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-12">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Admin-Scheduler</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="admin-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Scheduler</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">

                            <div class="col-sm-4 ml-5">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Create Schedule <i class="feather icon-plus-circle"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="../../controllers/auto-scheduler.php">
                                            <div class="form-group">
                                                <label for="name">Semester</label>
                                                <select type="text" name="semester" class="form-control" id="semester" placeholder="Enter Semester" required>
                                                    <option value="1">1st</option>
                                                    <option value="2">2nd</option>
                                                    <option value="midyear">Midyear</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Schedule Type</label>
                                                <select type="text" name="type" class="form-control" id="type" placeholder="Enter Schedule Type" required>
                                                    <option value="none">Class Schedule</option>
                                                    <option value="prelim">Preliminary Exam</option>
                                                    <option value="midterm">Midterm Exam</option>
                                                    <option value="final">Final Exam</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Academic Year</label>
                                                <input type="text" name="academic_year" class="form-control" id="academicYear" placeholder="Enter Academic Year (e.g. 2024-2025)" required>
                                            </div>

                                            <button type="submit" name="submit" class="btn btn-primary">Create</button>
                                        </form>

                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Get Schedule <i class="feather icon-navigation"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" id="getScheduleForm">
                                            <div class="form-group">
                                                <label for="name">Semester</label>
                                                <select type="text" name="semester" class="form-control" id="getSemester" placeholder="Enter Semester" required>
                                                    <option value="1">1st</option>
                                                    <option value="2">2nd</option>
                                                    <option value="midyear">Midyear</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Schedule Type</label>
                                                <select type="text" name="type" class="form-control" id="getType" placeholder="Enter Schedule Type" required>
                                                    <option value="none">Class Schedule</option>
                                                    <option value="prelim">Preliminary Exam</option>
                                                    <option value="midterm">Midterm Exam</option>
                                                    <option value="final">Final Exam</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="enrollSection">Section</label>
                                                <select id="getEnrollSection" class="form-control" required>
                                                    <!-- Options will be populated by JavaScript -->
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Academic Year</label>
                                                <input type="text" name="academic_year" class="form-control" id="getAcademicYear" placeholder="Enter Academic Year (e.g. 2024-2025)" required>
                                            </div>

                                            <button type="submit" name="submit" class="btn btn-primary">Search</button>
                                            <button type="button" class="btn btn-primary" id="printButton">Print Schedule</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Delete Schedule <i class="feather icon-trash"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" id="deleteScheduleForm">
                                            <div class="form-group">
                                                <label for="name">Semester</label>
                                                <select type="text" name="semester" class="form-control" id="deleteSemester" placeholder="Enter Semester" required>
                                                    <option value="1">1st</option>
                                                    <option value="2">2nd</option>
                                                    <option value="midyear">Midyear</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Schedule Type</label>
                                                <select type="text" name="type" class="form-control" id="deleteType" placeholder="Enter Schedule Type" required>
                                                    <option value="none">Class Schedule</option>
                                                    <option value="prelim">Preliminary Exam</option>
                                                    <option value="midterm">Midterm Exam</option>
                                                    <option value="final">Final Exam</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="name">Academic Year</label>
                                                <input type="text" name="academic_year" class="form-control" id="deleteAcademicYear" placeholder="Enter Academic Year (e.g. 2024-2025)" required>
                                            </div>

                                            <button type="button" class="btn btn-danger" id="deleteButton">Delete <i class="feather icon-trash-2"></i></button>
                                        </form>

                                        <!-- Modal -->
                                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete <br><span id="deleteDetails"></span>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <!-- [ schedule table ] start -->


                            <div class="container mt-4 pt-5 bg-white p-4" id="scheduleContainer">
                                <h3 class="text-center" id="scheduleTypeHeader">CLASS SCHEDULE</h3>
                                <h5 class="text-center" id="scheduleHeader">Second Semester, SY: 2024 - 2025</h5>
                                <h6 class="text-center" id="sectionHeader">BSIT-1A</h6>

                                <table class="table table-bordered" id="scheduleTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="time-column">Time</th>
                                            <th class="day-column">Monday</th>
                                            <th class="day-column">Tuesday</th>
                                            <th class="day-column">Wednesday</th>
                                            <th class="day-column">Thursday</th>
                                            <th class="day-column">Friday</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Schedule will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                        </div>


                        <!-- [ schedule table ] end -->




                    </div>
                    <!-- [ Main Content ] end -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->


<script>
    // Delete Schedule
    document.getElementById('deleteButton').addEventListener('click', function() {
        const semester = document.getElementById('deleteSemester').value;
        const type = document.getElementById('deleteType').value;
        const academicYear = document.getElementById('deleteAcademicYear').value;
        const details = `${type === 'none' ? 'Class' : type === 'prelim' ? 'Preliminary Exam' : type === 'midterm' ? 'Midterm Exam' : type === 'final' ? 'Final Exam' : ''} Schedule for ${semester === '1' ? '1st Semester' : semester === '2' ? '2nd Semester' : semester === 'midyear' ? 'Midyear' : ''}, Academic Year: ${academicYear}`;
        document.getElementById('deleteDetails').innerText = details;
        $('#confirmDeleteModal').modal('show');
    });

    document.getElementById('confirmDeleteButton').addEventListener('click', function() {
        const semester = document.getElementById('deleteSemester').value;
        const type = document.getElementById('deleteType').value;
        const academicYear = document.getElementById('deleteAcademicYear').value;

        fetch('../../controllers/delete-schedule.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    semester: semester,
                    type: type,
                    academic_year: academicYear
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // First, get the raw response as text
            })
            .then(text => {
                console.log('Raw response:', text); // Log the raw response
                return JSON.parse(text); // Now try to parse it as JSON
            })
            .then(data => {
                if (data.success) {
                    showAlert('Schedule deleted successfully', 'success');
                } else {
                    showAlert('Error deleting schedule: ' + data.message, 'danger');
                }
                $('#confirmDeleteModal').modal('hide');
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error deleting schedule: ' + error.message, 'danger');
                $('#confirmDeleteModal').modal('hide');
            });
    });
    // Get Schedule
    document.getElementById('getScheduleForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const section = document.getElementById('getEnrollSection').value;
        const academicYear = document.getElementById('getAcademicYear').value;

        fetch(`../../controllers/get-schedules.php?semester=${semester}&type=${type}&section=${section}&academic_year=${academicYear}`)
            .then(response => {
                if (!response.ok) {
                    // Parse the JSON error message from the response
                    return response.json().then(errorData => {
                        // Throw an error with the message from the server
                        throw new Error(errorData.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Fetched data:', data); // Debugging: Log the fetched data

                const scheduleTable = document.getElementById('scheduleTable').getElementsByTagName('tbody')[0];
                scheduleTable.innerHTML = ''; // Clear existing rows

                // Populate the table
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td class="${row.time === '12:00 PM - 01:00 PM' ? 'lunch-break' : ''}">${row.time}</td>
                    <td class="${row.monday === 'Lunch Break' ? 'lunch-break' : ''}">${row.monday}</td>
                    <td class="${row.tuesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.tuesday}</td>
                    <td class="${row.wednesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.wednesday}</td>
                    <td class="${row.thursday === 'Lunch Break' ? 'lunch-break' : ''}">${row.thursday}</td>
                    <td class="${row.friday === 'Online Class' ? 'online-class' : ''} ${row.friday === 'Lunch Break' ? 'lunch-break' : ''}">${row.friday}</td>
                `;
                    scheduleTable.appendChild(tr);
                });

                // Update headers
                document.getElementById('scheduleHeader').innerText = `${type === 'none' ? 'Class' : 'prelim' ? 'Preliminary Exam' : 'midterm' ? 'Midterm Exam' : 'final' ? 'Final Exam' : ''} Schedule, SY: ${academicYear}`;
                document.getElementById('sectionHeader').innerText = window.sections[section];
                document.getElementById('scheduleTypeHeader').innerText = `${semester === 1 ? '1st Semester' : 2 ? '2nd Semester' : 3 ? 'Midyear' : ''} ${type === 'none' ? 'Class' : 'prelim' ? 'Prelims' : 'midterm' ? 'Midterms' : 'final' ? 'Finals' : ''} Schedule`;
            })
            .catch(error => {
                console.error('Error fetching schedule:', error);
                showAlert('Error fetching schedule: ' + error, 'danger');
            });
    });
    // Print Schedule
    document.getElementById('printButton').addEventListener('click', function() {
        const div = document.getElementById('scheduleContainer');

        // Get the values from the form inputs
        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const section = document.getElementById('getEnrollSection').value;
        const academicYear = document.getElementById('getAcademicYear').value;

        // Construct the filename
        const fileName = `${window.sections[section]} ${semester === 1 ? '1st Semester' : 2 ? '2nd Semester' : 3 ? 'Midyear' : ''} ${type === 'none' ? 'Class' : 'prelim' ? 'Preliminary Exam' : 'midterm' ? 'Midterm Exam' : 'final' ? 'Final Exam' : ''} Schedule SY;${academicYear}.pdf`;

        // Use html2canvas to capture the div as an image
        html2canvas(div).then((canvas) => {
            const imgData = canvas.toDataURL('image/png'); // Convert canvas to image data URL

            // Create a new PDF document in landscape orientation
            const pdf = new jspdf.jsPDF({
                orientation: 'landscape', // Set orientation to landscape
                unit: 'mm', // Unit of measurement (millimeters)
                format: 'a4', // Paper size (A4)
            });

            // Get the dimensions of the image and the PDF page
            const imgWidth = pdf.internal.pageSize.getWidth(); // Full width of the PDF page
            const imgHeight = (canvas.height * imgWidth) / canvas.width; // Calculate height to maintain aspect ratio

            // Add the image to the PDF
            pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);

            // Save the PDF with the dynamically constructed filename
            pdf.save(fileName);
        });
    });
    // Fetch all sections
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-sections.php')
            .then(response => response.json())
            .then(data => {
                const enrollSection = document.getElementById('getEnrollSection');
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

    function showAlert(message, type = 'success') {
        // Remove existing alert if any
        let existingAlert = document.querySelector('.floating-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        // Create the alert element
        let alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show floating-alert `;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <strong>${type === 'success' ? 'Success' : type === 'warning' ? 'Warning' : 'danger' ? 'Error' : 'Error'}!</strong> ${message}
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
        }, 15000);
    }

    // Remove ?msg or ?error from URL
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
<?php
if (isset($_GET['msg'])) {
    echo "<script>showAlert('{$_GET['msg']}')</script>";
}
if (isset($_GET['error'])) {
    echo "<script>showAlert('{$_GET['error']}', 'danger')</script>";
}
if (isset($_GET['warning'])) {
    echo "<script>showAlert('{$_GET['warning']}', 'warning')</script>";
}
?>
<?php @include 'footer.php'; ?>