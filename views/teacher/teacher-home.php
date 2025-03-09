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

    .pcoded-main-container {
        margin-left: -30px !important;
        max-width: 100% !important;
    }
</style>

<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
    <div class="pcoded-wrapper container">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-12">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Teacher Class Schedule</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="teacher-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Home</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- [ Main Content ] start -->
                        <div class="row">




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





                                            <button type="submit" name="submit" class="btn btn-primary">Search</button>
                                            <button type="button" class="btn btn-primary" id="printButton">Print Schedule</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- [ teacher-table ] start -->
                            <div class="col-sm-9">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5>Subjects Assigned <i class="feather icon-folder"></i></h5>

                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="teacherTable">
                                                <thead class="thead-dark">
                                                    <tr>

                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Gender</th>

                                                        <th>Subject Code</th>
                                                        <th>Subject Name</th>
                                                        <th>Semester</th>


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


                        <div class="row">
                            <!-- [ schedule table ] start -->


                            <div class="container mt-4 pt-5 bg-white p-4" id="scheduleContainer">
                                <h3 class="text-center" id="scheduleTypeHeader">CLASS SCHEDULE</h3>
                                <h5 class="text-center" id="scheduleHeader">Second Semester, SY: 2024 - 2025</h5>


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



                    <!-- Update password modal -->
                    <div class="modal fade" id="updatePasswordModal" tabindex="-1" role="dialog" aria-labelledby="updatePasswordModal" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updatePasswordModal">Update Password</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="updatePasswordForm" action="../../controllers/update-password.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $_SESSION['id']; ?>">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirmPassword">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Fetch all teachers
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-teacher-subject.php?id=<?php echo $_SESSION['id']; ?>')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('teacherTableBody');
                data.forEach((teacher, index) => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-id', teacher.id);
                    row.innerHTML = `
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
                                                             `;
                    tbody.appendChild(row);
                });
                window.teachers = data;

            })
            .catch(error => console.error('Error fetching teachers:', error));
    });

    // Get Schedule
    document.getElementById('getScheduleForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const academicYear = window.teachers[0].academic_year;
        const teacherId = window.teachers[0].id;
        //console.log(semester, type, academicYear);

        fetch(`../../controllers/get-teacher-schedules.php?teacher_id=${teacherId}&semester=${semester}&type=${type}&academic_year=${academicYear}`)
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
        const academicYear = window.teachers[0].academic_year;
        const teacher = window.teachers[0].name;

        // Construct the filename
        const fileName = `${teacher} ${semester === 1 ? '1st Semester' : 2 ? '2nd Semester' : 3 ? 'Midyear' : ''} ${type === 'none' ? 'Class' : 'prelim' ? 'Preliminary Exam' : 'midterm' ? 'Midterm Exam' : 'final' ? 'Final Exam' : ''} Schedule SY;${academicYear}.pdf`;

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
    // Check if password is 123456
    document.addEventListener('DOMContentLoaded', function() {
        //fetch password
        fetch('../../controllers/get-password.php?id=<?php echo $_SESSION['id']; ?>')
            .then(response => response.json())
            .then(data => {
                console.log(data);
                //check if password is 123456
                if (data.password == '123456') {
                    //show change password modal
                    $('#updatePasswordModal').modal('show');

                }
            })

    })
    // Update password
    document.getElementById('updatePasswordForm').addEventListener('submit', function(event) {
        var password = document.getElementById('password').value;
        var confirmPassword = document.getElementById('confirmPassword').value;
        if (password !== confirmPassword) {
            event.preventDefault();
            showAlert('Passwords do not match', 'danger');
        }
    });

    console.log(window.students);
    // Get Schedule
    document.getElementById('getScheduleForm').addEventListener('submit', function(event) {
        event.preventDefault();



        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const section = window.students[0].section_id;
        const academicYear = window.students[0].academic_year;

        console.log(semester, type, section, academicYear);

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
                document.getElementById('sectionHeader').innerText = window.students[0].section;
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
        const section = window.students[0].section_id;
        const academicYear = window.students[0].academic_year;

        // Construct the filename
        const fileName = `${window.students[0].section} ${semester === 1 ? '1st Semester' : 2 ? '2nd Semester' : 3 ? 'Midyear' : ''} ${type === 'none' ? 'Class' : 'prelim' ? 'Preliminary Exam' : 'midterm' ? 'Midterm Exam' : 'final' ? 'Final Exam' : ''} Schedule SY;${academicYear}.pdf`;

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
    // Show alert
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
        }, 7000);
    }

    // Remove ?msg or ?error from URL
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>


<!-- [ Main Content ] end -->
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