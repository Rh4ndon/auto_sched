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

    /* Loading Animation Styles */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .loading-container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        text-align: center;
        min-width: 300px;
    }

    .loading-bar-container {
        width: 100%;
        height: 8px;
        background-color: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
        margin: 20px 0;
        position: relative;
    }

    .loading-bar {
        height: 100%;
        background: linear-gradient(90deg, #007bff, #0056b3);
        width: 0%;
        border-radius: 4px;
        transition: width 0.3s ease;
        position: relative;
    }

    .loading-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .loading-text {
        font-size: 16px;
        color: #333;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .loading-percentage {
        font-size: 14px;
        color: #666;
        margin-top: 10px;
        font-weight: bold;
    }

    .loading-spinner {
        margin-bottom: 15px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<style>
    /* Print-specific styles */
    @media print {
        body * {
            visibility: hidden;
        }

        #scheduleContainer,
        #scheduleContainer * {
            visibility: visible;
        }

        #scheduleContainer {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .dont-print {
            display: none !important;
        }

        /* Print-specific table adjustments */
        #scheduleTable {
            font-size: 9px;
            line-height: 1.1;
        }

        #scheduleTable th,
        #scheduleTable td {
            padding: 3px;
        }

        #scheduleTypeHeader {
            font-size: 14px;
            margin: 2px 0;
        }

        #scheduleHeader {
            font-size: 12px;
            margin: 2px 0;
        }

        #sectionHeader {
            font-size: 10px;
            margin: 2px 0 5px 0;
        }
    }
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-container">
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
        <div class="loading-text" id="loadingText">Processing...</div>
        <div class="loading-bar-container">
            <div class="loading-bar" id="loadingBar"></div>
        </div>
        <div class="loading-percentage" id="loadingPercentage">0%</div>
    </div>
</div>


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
                                        <form method="POST" action="../../controllers/auto-scheduler.php" id="createScheduleForm">
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

                                            <div class="card-footer d-flex justify-content-end">
                                                <button type="submit" name="submit" class="btn btn-primary">Search</button>
                                                <button type="button" class="btn btn-secondary" id="printButton">Print Schedule</button>
                                                <button type="button" class="btn btn-success" id="saveImageButton">Save as Image</button>
                                            </div>
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
                        <?php
                        session_start();
                        if (isset($_SESSION['scheduling_report'])) {
                            $report = $_SESSION['scheduling_report'];
                            unset($_SESSION['scheduling_report']);
                            echo "<div class='row mt-4'>";
                            echo "<div class='col-md-12'>";

                            echo "<div class='report-container'>";
                            echo "<h3>Scheduling Report</h3>";
                            echo "<p>Sections scheduled: " . $report['sections_scheduled'] . "</p>";

                            if (!empty($report['subjects_without_teachers'])) {
                                echo "<h4>Subjects Without Teachers:</h4>";
                                echo "<ul>";
                                foreach ($report['subjects_without_teachers'] as $subject) {
                                    echo "<li>{$subject['subject_code']} - {$subject['subject_name']} (Section: {$subject['section_name']})</li>";
                                }
                                echo "</ul>";
                            }


                            if (!empty($report['subjects_not_scheduled'])) {
                                echo "<h4>Detailed Scheduling Issues:</h4>";
                                echo "<p>Subjects not scheduled: " . $report['subjects_not_scheduled_count'] . "</p>";
                                echo "<table border='1'><tr><th>Subject</th><th>Section</th><th>Type</th><th>Minutes</th><th>Teacher ID</th><th>Scheduled slots</th><th>Required Slots</th><th>Reason</th></tr>";
                                foreach ($report['subjects_not_scheduled'] as $subject) {
                                    echo "<tr>";
                                    echo "<td>{$subject['subject_code']} - {$subject['subject_name']}</td>";
                                    echo "<td>{$subject['section_name']}</td>";
                                    echo "<td>{$subject['subject_type']}</td>";
                                    echo "<td>{$subject['minutes_per_week']}</td>";
                                    echo "<td>{$subject['teacher_id']}</td>";
                                    echo "<td>{$subject['num_slots_scheduled']}</td>";
                                    echo "<td>{$subject['required_slots']}</td>";
                                    echo "<td>{$subject['reason']}</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            }

                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>


                        <div class="row">
                            <!-- [ schedule table ] start -->


                            <div class="container mt-4 pt-5 bg-white p-4" id="scheduleContainer">
                                <h3 class="text-center" id="scheduleTypeHeader"></h3>
                                <h5 class="text-center" id="scheduleHeader"></h5>
                                <h6 class="text-center" id="sectionHeader"></h6>
                                <p class="text-center" id="notes" style="display: none;">
                                    Exam Date:
                                    <input type="date" placeholder="Date" id="notesDate" oninput="this.nextElementSibling.textContent = this.value ? new Date(this.value).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '';">
                                    <span style="margin-left:10px; font-weight:bold;"></span>
                                </p>

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
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="mx-3 mb-0">
                                        Prepared by:
                                        <span contenteditable="true" id="preparedBy" style="border-bottom:1px solid #000; min-width:200px; display:inline-block; outline:none;">

                                        </span>
                                    </p>
                                    <p class="mx-3 mb-0"> Reviewed by:
                                        <span contenteditable="true" id="reviewedBy" style="border-bottom:1px solid #000; min-width:200px; display:inline-block; outline:none;">
                                        </span>
                                    </p>

                                    <p class="mx-3 mb-0"> Approved by:
                                        <span contenteditable="true" id="approvedBy" style="border-bottom:1px solid #000; min-width:200px; display:inline-block; outline:none;">

                                        </span>
                                    </p>
                                </div>
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
    // Loading Animation Functions
    function showLoading(text = 'Processing...') {
        document.getElementById('loadingText').textContent = text;
        document.getElementById('loadingBar').style.width = '0%';
        document.getElementById('loadingPercentage').textContent = '0%';
        document.getElementById('loadingOverlay').style.display = 'flex';

        // Animate the loading bar
        animateLoadingBar();
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    function animateLoadingBar() {
        const loadingBar = document.getElementById('loadingBar');
        const loadingPercentage = document.getElementById('loadingPercentage');
        let progress = 0;

        const interval = setInterval(() => {
            progress += Math.random() * 15 + 5; // Random increment between 5-20
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
            }

            loadingBar.style.width = progress + '%';
            loadingPercentage.textContent = Math.round(progress) + '%';
        }, 100);
    }

    function updateLoadingProgress(percentage, text) {
        document.getElementById('loadingBar').style.width = percentage + '%';
        document.getElementById('loadingPercentage').textContent = Math.round(percentage) + '%';
        if (text) {
            document.getElementById('loadingText').textContent = text;
        }
    }

    // Create Schedule with Loading
    document.getElementById('createScheduleForm').addEventListener('submit', function(event) {
        showLoading('Creating Schedule...');
        // Let the form submit normally, the loading will be hidden when page reloads
    });

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
        showLoading('Deleting Schedule...');
        $('#confirmDeleteModal').modal('hide');

        const semester = document.getElementById('deleteSemester').value;
        const type = document.getElementById('deleteType').value;
        const academicYear = document.getElementById('deleteAcademicYear').value;

        // Simulate progress updates
        setTimeout(() => updateLoadingProgress(30, 'Validating request...'), 300);
        setTimeout(() => updateLoadingProgress(60, 'Removing schedule data...'), 600);
        setTimeout(() => updateLoadingProgress(90, 'Finalizing deletion...'), 900);

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
                updateLoadingProgress(100, 'Processing response...');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                return JSON.parse(text);
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    showAlert('Schedule deleted successfully', 'success');
                } else {
                    showAlert('Error deleting schedule: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                showAlert('Error deleting schedule: ' + error.message, 'danger');
            });
    });

    // Get Schedule with Loading
    document.getElementById('getScheduleForm').addEventListener('submit', function(event) {
        event.preventDefault();

        showLoading('Fetching Schedule...');

        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const section = document.getElementById('getEnrollSection').value;
        const academicYear = document.getElementById('getAcademicYear').value;

        // Simulate progress updates
        setTimeout(() => updateLoadingProgress(25, 'Connecting to database...'), 200);
        setTimeout(() => updateLoadingProgress(50, 'Retrieving schedule data...'), 400);
        setTimeout(() => updateLoadingProgress(75, 'Processing schedule...'), 600);

        fetch(`../../controllers/get-schedules.php?semester=${semester}&type=${type}&section=${section}&academic_year=${academicYear}`)
            .then(response => {
                updateLoadingProgress(90, 'Loading schedule table...');
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                updateLoadingProgress(95, 'Rendering schedule...');
                console.log('Fetched data:', data);

                const scheduleTable = document.getElementById('scheduleTable').getElementsByTagName('tbody')[0];
                scheduleTable.innerHTML = '';

                // Populate the table
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td contenteditable="true" class="${row.time === '12:20 PM - 01:20 PM' ? 'lunch-break' : ''}">${row.time}</td>
                    <td contenteditable="true" class="${row.monday === 'Lunch Break' ? 'lunch-break' : ''}">${row.monday}</td>
                    <td contenteditable="true" class="${row.tuesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.tuesday}</td>
                    <td contenteditable="true" class="${row.wednesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.wednesday}</td>
                    <td contenteditable="true" class="${row.thursday === 'Lunch Break' ? 'lunch-break' : ''}">${row.thursday}</td>
                    <td contenteditable="true" class=" ${row.friday === 'Lunch Break' ? 'lunch-break' : ''}">${row.friday}</td>
                    
                `;
                    scheduleTable.appendChild(tr);
                });

                // Update headers
                let typeText = '';
                if (type === 'none') typeText = 'Class';
                else if (type === 'prelim') typeText = 'Preliminary Exam';
                else if (type === 'midterm') typeText = 'Midterm Exam';
                else if (type === 'final') typeText = 'Final Exam';

                let typeShortText = '';
                if (type === 'none') typeShortText = 'Class';
                else if (type === 'prelim') typeShortText = 'Prelims';
                else if (type === 'midterm') typeShortText = 'Midterms';
                else if (type === 'final') typeShortText = 'Finals';

                let semesterText = '';
                if (semester === '1') semesterText = '1st Semester';
                else if (semester === '2') semesterText = '2nd Semester';
                else if (semester === 'midyear') semesterText = 'Midyear';

                document.getElementById('scheduleHeader').innerText = `${typeText} Schedule, SY: ${academicYear}`;
                document.getElementById('sectionHeader').innerText = window.sections[section];
                document.getElementById('scheduleTypeHeader').innerText = `${semesterText} ${typeShortText} Schedule`;

                // Show the schedule table

                if (type !== 'none') {
                    document.getElementById('notes').style.display = 'block';
                }


                updateLoadingProgress(100, 'Complete!');
                setTimeout(() => {
                    hideLoading();
                }, 300);
            })
            .catch(error => {
                console.error('Error fetching schedule:', error);
                hideLoading();
                showAlert('Error fetching schedule: ' + error, 'danger');
            });
    });


    // Fetch all sections with Loading
    document.addEventListener('DOMContentLoaded', function() {
        showLoading('Loading sections...');

        setTimeout(() => updateLoadingProgress(50, 'Fetching section data...'), 200);

        fetch('../../controllers/get-sections.php')
            .then(response => {
                updateLoadingProgress(80, 'Processing sections...');
                return response.json();
            })
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

                updateLoadingProgress(100, 'Ready!');
                setTimeout(() => {
                    hideLoading();
                }, 300);
            })
            .catch(error => {
                console.error('Error fetching sections:', error);
                hideLoading();
                showAlert('Error loading sections: ' + error.message, 'danger');
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

<script>
    document.getElementById('printButton').addEventListener('click', function() {
        showLoading('Preparing PDF...');

        // Get the values from the form inputs
        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const section = document.getElementById('getEnrollSection').value;
        const academicYear = document.getElementById('getAcademicYear').value;
        const sectionText = window.sections[section];

        const preparedBy = document.getElementById('preparedBy').innerText;
        const reviewedBy = document.getElementById('reviewedBy').innerText;
        const approvedBy = document.getElementById('approvedBy').innerText;

        // Check if schedule data exists
        const scheduleTable = document.getElementById('scheduleTable').getElementsByTagName('tbody')[0];
        if (!scheduleTable || scheduleTable.children.length === 0) {
            hideLoading();
            showAlert('Please search for a schedule first before printing.', 'warning');
            return;
        }

        // Construct the filename
        let typeText = '';
        if (type === 'none') typeText = 'Class';
        else if (type === 'prelim') typeText = 'Preliminary Exam';
        else if (type === 'midterm') typeText = 'Midterm Exam';
        else if (type === 'final') typeText = 'Final Exam';

        let typeShortText = '';
        if (type === 'none') typeShortText = 'Class';
        else if (type === 'prelim') typeShortText = 'Prelims';
        else if (type === 'midterm') typeShortText = 'Midterms';
        else if (type === 'final') typeShortText = 'Finals';

        let semesterText = '';
        if (semester === '1') semesterText = '1st Semester';
        else if (semester === '2') semesterText = '2nd Semester';
        else if (semester === 'midyear') semesterText = 'Midyear';

        const fileName = `${sectionText} ${semesterText} ${typeText} Schedule SY ${academicYear}.pdf`;

        // Extract schedule data from the table
        const scheduleData = [];
        const rows = scheduleTable.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            if (cells.length >= 6) {
                scheduleData.push({
                    time: cells[0].textContent.trim(),
                    monday: cells[1].textContent.trim(),
                    tuesday: cells[2].textContent.trim(),
                    wednesday: cells[3].textContent.trim(),
                    thursday: cells[4].textContent.trim(),
                    friday: cells[5].textContent.trim()
                });
            }
        }

        // Check if exam date is needed and provided
        if (type !== 'none') {
            const examDateInput = document.getElementById('notesDate');
            if (!examDateInput.value) {
                hideLoading();
                showAlert('Please enter the Exam Date before printing.', 'warning');
                examDateInput.style.display = '';
                examDateInput.focus();
                return;
            }
        }

        // Simulate progress updates
        setTimeout(() => updateLoadingProgress(30, 'Processing schedule data...'), 200);
        setTimeout(() => updateLoadingProgress(60, 'Creating PDF layout...'), 500);

        // Create PDF with custom layout
        const pdf = new jspdf.jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
        });

        // PDF dimensions
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 10;
        const contentWidth = pageWidth - (margin * 2);
        const contentHeight = pageHeight - (margin * 2);

        let currentY = margin;

        // Helper function to add text with word wrapping
        function addWrappedText(text, x, y, maxWidth, lineHeight = 4) {
            if (!text || text === '') return y;

            const lines = pdf.splitTextToSize(text, maxWidth);
            for (let i = 0; i < lines.length; i++) {
                if (currentY + lineHeight > pageHeight - margin) {
                    pdf.addPage();
                    currentY = margin;
                }
                pdf.text(lines[i], x, currentY);
                currentY += lineHeight;
            }
            return currentY;
        }

        // Helper function to check if we need a new page
        function checkNewPage(requiredHeight) {
            if (currentY + requiredHeight > pageHeight - margin) {
                pdf.addPage();
                currentY = margin;
            }
        }

        try {
            // Title section
            pdf.setFontSize(16);
            pdf.setFont('helvetica', 'bold');
            const title = `${semesterText} ${typeShortText} Schedule`;
            const titleWidth = pdf.getTextWidth(title);
            pdf.text(title, (pageWidth - titleWidth) / 2, currentY);
            currentY += 8;

            // Subtitle
            pdf.setFontSize(12);
            pdf.setFont('helvetica', 'normal');
            const subtitle = `${typeText} Schedule, SY: ${academicYear}`;
            const subtitleWidth = pdf.getTextWidth(subtitle);
            pdf.text(subtitle, (pageWidth - subtitleWidth) / 2, currentY);
            currentY += 6;

            // Section info
            pdf.setFontSize(10);
            const sectionInfo = `Section: ${sectionText}`;
            const sectionInfoWidth = pdf.getTextWidth(sectionInfo);
            pdf.text(sectionInfo, (pageWidth - sectionInfoWidth) / 2, currentY);
            currentY += 6;

            // Exam date if applicable
            if (type !== 'none') {
                const examDateInput = document.getElementById('notesDate');
                const examDate = examDateInput.value ? new Date(examDateInput.value).toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                }) : '';

                if (examDate) {
                    pdf.setFontSize(10);
                    const examDateText = `Exam Date: ${examDate}`;
                    const examDateWidth = pdf.getTextWidth(examDateText);
                    pdf.text(examDateText, (pageWidth - examDateWidth) / 2, currentY);
                    currentY += 6;
                }
            }

            currentY += 4;

            // Table setup
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            const timeColWidth = 28; // Slightly wider for better time display
            const dayColWidth = (contentWidth - timeColWidth) / 5;

            // Table header
            checkNewPage(15);
            pdf.setFontSize(9);
            pdf.setFont('helvetica', 'bold');

            // Draw header background
            pdf.setFillColor(240, 240, 240);
            pdf.rect(margin, currentY - 3, contentWidth, 8, 'F');

            // Header borders
            pdf.setDrawColor(0, 0, 0);
            pdf.setLineWidth(0.1);

            // Time column header
            pdf.rect(margin, currentY - 3, timeColWidth, 8);
            pdf.text('Time', margin + 2, currentY + 2);

            // Day column headers
            for (let i = 0; i < days.length; i++) {
                const x = margin + timeColWidth + (i * dayColWidth);
                pdf.rect(x, currentY - 3, dayColWidth, 8);
                const dayText = days[i];
                const dayTextWidth = pdf.getTextWidth(dayText);
                pdf.text(dayText, x + (dayColWidth - dayTextWidth) / 2, currentY + 2);
            }

            currentY += 8;

            // Table rows
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(8);

            for (let rowIndex = 0; rowIndex < scheduleData.length; rowIndex++) {
                const row = scheduleData[rowIndex];

                // Calculate row height based on content with better spacing
                let maxLines = 1;
                const dayContents = [row.monday, row.tuesday, row.wednesday, row.thursday, row.friday];

                for (let content of dayContents) {
                    if (content && content !== '') {
                        const lines = pdf.splitTextToSize(content, dayColWidth - 6); // More padding
                        maxLines = Math.max(maxLines, lines.length);
                    }
                }

                const rowHeight = Math.max(8, maxLines * 4 + 3); // Better minimum height and spacing

                // Check if we need a new page
                checkNewPage(rowHeight + 2);

                const rowStartY = currentY;

                // Determine if this is a lunch break row
                const isLunchBreak = row.time.includes('12:20 PM - 01:20 PM') ||
                    row.monday === 'Lunch Break' ||
                    row.tuesday === 'Lunch Break' ||
                    row.wednesday === 'Lunch Break' ||
                    row.thursday === 'Lunch Break' ||
                    row.friday === 'Lunch Break';

                // Set background color for lunch break
                if (isLunchBreak) {
                    pdf.setFillColor(255, 204, 203); // Light red
                    pdf.rect(margin, rowStartY, contentWidth, rowHeight, 'F');
                }

                // Draw row borders
                pdf.setDrawColor(0, 0, 0);
                pdf.setLineWidth(0.1);

                // Time column with better formatting
                pdf.rect(margin, rowStartY, timeColWidth, rowHeight);
                const timeLines = pdf.splitTextToSize(row.time, timeColWidth - 6);
                let textY = rowStartY + 4;
                pdf.setFont('helvetica', 'bold'); // Make time bold
                for (let line of timeLines) {
                    pdf.text(line, margin + 3, textY);
                    textY += 4;
                }

                // Day columns
                for (let i = 0; i < days.length; i++) {
                    const x = margin + timeColWidth + (i * dayColWidth);
                    const content = dayContents[i];

                    pdf.rect(x, rowStartY, dayColWidth, rowHeight);

                    if (content && content !== '' && content !== 'Lunch Break') {
                        const lines = pdf.splitTextToSize(content, dayColWidth - 6); // More padding
                        textY = rowStartY + 4; // Better starting position

                        for (let line of lines) {
                            // Handle different content types with styling
                            if (content.toLowerCase().includes('online')) {
                                pdf.setFont('helvetica', 'bold');
                            } else if (content.toLowerCase().includes('lab')) {
                                pdf.setFont('helvetica', 'italic');
                            } else if (content.toLowerCase().includes('pe') || content.toLowerCase().includes('physical education')) {
                                pdf.setFont('helvetica', 'bold');
                            } else {
                                pdf.setFont('helvetica', 'normal');
                            }

                            pdf.text(line, x + 3, textY); // Better padding
                            textY += 4; // Better line spacing
                        }
                    } else if (content === 'Lunch Break') {
                        pdf.setFont('helvetica', 'bold'); // Make lunch break bold
                        const lunchText = 'Lunch Break';
                        const lunchWidth = pdf.getTextWidth(lunchText);
                        pdf.text(lunchText, x + (dayColWidth - lunchWidth) / 2, rowStartY + rowHeight / 2 + 1);
                    }
                }

                currentY += rowHeight;
            }

            updateLoadingProgress(90, 'Finalizing PDF...');

            // Add footer with generation timestamp
            const now = new Date();
            const timestamp = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
            pdf.setFontSize(7);
            pdf.setFont('helvetica', 'normal');

            // Add signature lines below the table
            // Calculate signature Y position based on table size
            let sigStartY = currentY + 15;
            // If too close to bottom, move to next page
            if (sigStartY + 20 > pageHeight - margin) {
                pdf.addPage();
                sigStartY = margin + 10;
            }
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'normal');
            pdf.text(`Prepared by: ${preparedBy}`, margin, sigStartY);
            pdf.text(`Reviewed by: ${reviewedBy}`, margin + 60, sigStartY);
            pdf.text(`Approved by: ${approvedBy}`, margin + 120, sigStartY);


            // Draw signature lines
            pdf.line(margin, sigStartY + 3, margin + 40, sigStartY + 3);
            pdf.line(margin + 60, sigStartY + 3, margin + 100, sigStartY + 3);
            pdf.line(margin + 120, sigStartY + 3, margin + 160, sigStartY + 3);

            pdf.text(`Generated on: ${timestamp}`, margin, pageHeight - 5);

            updateLoadingProgress(100, 'Download ready!');

            // Save the PDF
            setTimeout(() => {
                pdf.save(fileName);
                hideLoading();
                showAlert('PDF generated successfully!', 'success');
            }, 500);

        } catch (error) {
            console.error('Error generating PDF:', error);
            hideLoading();
            showAlert('Error generating PDF: ' + error.message, 'danger');
        }
    });
</script>
<script>
    // Save as Image functionality
    document.getElementById('saveImageButton').addEventListener('click', function() {
        showLoading('Generating image...');

        // Get the schedule container
        const element = document.getElementById('scheduleContainer');

        // Get the values from the form inputs for filename
        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const sectionId = document.getElementById('getEnrollSection').value;
        const sectionText = window.sections[sectionId] || 'Section';
        const academicYear = document.getElementById('getAcademicYear').value;

        // Construct the filename
        let typeText = '';
        if (type === 'none') typeText = 'Class';
        else if (type === 'prelim') typeText = 'Preliminary Exam';
        else if (type === 'midterm') typeText = 'Midterm Exam';
        else if (type === 'final') typeText = 'Final Exam';

        let semesterText = '';
        if (semester === '1') semesterText = '1st Semester';
        else if (semester === '2') semesterText = '2nd Semester';
        else if (semester === 'midyear') semesterText = 'Midyear';

        const fileName = `${sectionText} ${semesterText} ${typeText} Schedule SY ${academicYear}.png`;

        // Options for html2canvas - improved configuration
        const options = {
            scale: 2, // Higher scale for better quality
            logging: true,
            useCORS: true,
            allowTaint: true,
            scrollX: 0,
            scrollY: -window.scrollY,
            windowWidth: document.documentElement.offsetWidth,
            windowHeight: element.offsetHeight + 100,
            backgroundColor: '#FFFFFF', // Ensure white background
            ignoreElements: function(element) {
                // Ignore elements that might cause issues
                return element.id === 'notesDate';
            }
        };

        // Simulate progress updates
        setTimeout(() => updateLoadingProgress(30, 'Capturing schedule...'), 200);
        setTimeout(() => updateLoadingProgress(60, 'Processing image...'), 500);

        // Use html2canvas to capture the element
        html2canvas(element, options).then(canvas => {
            updateLoadingProgress(90, 'Finalizing image...');

            // Convert canvas to image
            const image = canvas.toDataURL('image/png');

            // Create a temporary link to download the image
            const link = document.createElement('a');
            link.href = image;
            link.download = fileName;

            // Trigger the download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            updateLoadingProgress(100, 'Image saved!');
            setTimeout(() => {
                hideLoading();
                showAlert('Schedule saved as image successfully!', 'success');
            }, 500);
        }).catch(error => {
            console.error('Error generating image:', error);
            hideLoading();
            showAlert('Error saving image: ' + error.message, 'danger');
        });
    });
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