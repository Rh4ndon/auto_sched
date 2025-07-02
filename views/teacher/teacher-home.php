<?php @include 'header.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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





                                            <div class="card-footer d-flex justify-content-end">
                                                <button type="submit" name="submit" class="btn btn-primary">Search</button>
                                                <button type="button" class="btn btn-secondary" id="printButton">Print Schedule</button>
                                                <button type="button" class="btn btn-success" id="saveImageButton">Save as Image</button>
                                            </div>
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
                    <td contenteditable="true" class="${row.time === '12:00 PM - 01:00 PM' ? 'lunch-break' : ''}">${row.time}</td>
                    <td contenteditable="true" class="${row.monday === 'Lunch Break' ? 'lunch-break' : ''}">${row.monday}</td>
                    <td contenteditable="true" class="${row.tuesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.tuesday}</td>
                    <td contenteditable="true" class="${row.wednesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.wednesday}</td>
                    <td contenteditable="true" class="${row.thursday === 'Lunch Break' ? 'lunch-break' : ''}">${row.thursday}</td>
                      <td contenteditable="true" class="${row.thursday === 'Lunch Break' ? 'lunch-break' : ''}">${row.friday}</td>
                `;
                    scheduleTable.appendChild(tr);
                });

                // Update headers using readable variables
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
                document.getElementById('scheduleTypeHeader').innerText = `${semesterText} ${typeShortText} Schedule`;

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

        // Construct the filename using the same logic as in the headers
        let typeText = '';
        if (type === 'none') typeText = 'Class';
        else if (type === 'prelim') typeText = 'Preliminary Exam';
        else if (type === 'midterm') typeText = 'Midterm Exam';
        else if (type === 'final') typeText = 'Final Exam';

        let semesterText = '';
        if (semester === '1') semesterText = '1st Semester';
        else if (semester === '2') semesterText = '2nd Semester';
        else if (semester === 'midyear') semesterText = 'Midyear';

        const fileName = `${teacher} ${semesterText} ${typeText} Schedule SY ${academicYear}.pdf`;

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

<script>
    // Print Schedule with Loading
    document.getElementById('printButton').addEventListener('click', function() {


        // Get the values from the form inputs
        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const academicYear = window.teachers[0].academic_year;
        const teacherName = window.teachers[0].name;

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

        const fileName = `${teacherName} ${semesterText} ${typeText} Schedule SY ${academicYear}.pdf`;

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

            // Teacher info
            pdf.setFontSize(10);
            const teacherInfo = `Teacher: ${teacherName}`;
            const teacherInfoWidth = pdf.getTextWidth(teacherInfo);
            pdf.text(teacherInfo, (pageWidth - teacherInfoWidth) / 2, currentY);
            currentY += 10;

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
                const isLunchBreak = row.time.includes('12:00 PM - 01:00 PM') ||
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



            // Add footer with generation timestamp
            const now = new Date();
            const timestamp = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
            pdf.setFontSize(7);
            pdf.setFont('helvetica', 'normal');
            pdf.text(`Generated on: ${timestamp}`, margin, pageHeight - 5);



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


    // Save as Image functionality
    document.getElementById('saveImageButton').addEventListener('click', function() {


        // Get the schedule container
        const element = document.getElementById('scheduleContainer');

        // Get the values from the form inputs for filename
        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const academicYear = window.teachers[0].academic_year;
        const teacherName = window.teachers[0].name;

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

        const fileName = `${teacherName} ${semesterText} ${typeText} Schedule SY ${academicYear}.png`;

        // Options for html2canvas
        const options = {
            scale: 2, // Higher scale for better quality
            logging: true,
            useCORS: true,
            allowTaint: true,
            scrollX: 0,
            scrollY: -window.scrollY,
            windowWidth: document.documentElement.offsetWidth,
            windowHeight: element.offsetHeight + 100
        };


        // Use html2canvas to capture the element
        html2canvas(element, options).then(canvas => {


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