<?php @include 'header.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Custom styles for the schedule -->
<style>
    #scheduleTable td {
        word-wrap: break-word;
        word-break: break-word;
        white-space: normal;
        max-width: 200px;
        /* Adjust as needed */
        vertical-align: top;
        padding: 8px;
        line-height: 1.4;
    }

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


<!-- Update your print button script -->


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
                        <div class="row justify-content-center">
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Get Classroom Schedule <i class="feather icon-navigation"></i> </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" id="getScheduleForm">
                                            <div class="form-group">
                                                <label for="classroom">Classroom</label>
                                                <select id="getClassroom" class="form-control" required>
                                                    <!-- Options will be populated by JavaScript -->
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="semester">Semester</label>
                                                <select type="text" name="semester" class="form-control" id="getSemester" placeholder="Enter Semester" required>
                                                    <option value="1">1st</option>
                                                    <option value="2">2nd</option>
                                                    <option value="midyear">Midyear</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="type">Schedule Type</label>
                                                <select type="text" name="type" class="form-control" id="getType" placeholder="Enter Schedule Type" required>
                                                    <option value="none">Class Schedule</option>
                                                    <option value="prelim">Preliminary Exam</option>
                                                    <option value="midterm">Midterm Exam</option>
                                                    <option value="final">Final Exam</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="academic_year">Academic Year</label>
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
                        </div>



                        <div class="row">
                            <!-- [ schedule table ] start -->


                            <div class="container mt-4 pt-5 bg-white p-4" id="scheduleContainer">
                                <h3 class="text-center" id="scheduleTypeHeader"></h3>
                                <h5 class="text-center" id="scheduleHeader"></h5>
                                <h6 class="text-center" id="sectionHeader"></h6>

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


    // Store the last fetched schedule data globally
    let lastFetchedScheduleData = null;


    // Get Schedule with Loading
    document.getElementById('getScheduleForm').addEventListener('submit', function(event) {
        event.preventDefault();

        showLoading('Fetching Schedule...');

        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const classroom = document.getElementById('getClassroom').value;
        const academicYear = document.getElementById('getAcademicYear').value;
        const classroomText = document.getElementById('getClassroom').options[document.getElementById('getClassroom').selectedIndex].text;

        // Simulate progress updates
        setTimeout(() => updateLoadingProgress(25, 'Connecting to database...'), 200);
        setTimeout(() => updateLoadingProgress(50, 'Retrieving schedule data...'), 400);
        setTimeout(() => updateLoadingProgress(75, 'Processing schedule...'), 600);

        fetch(`../../controllers/get-room-schedules.php?semester=${semester}&type=${type}&classroom=${classroom}&academic_year=${academicYear}`)
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

                // Store the fetched data for printing
                lastFetchedScheduleData = data;


                const scheduleTable = document.getElementById('scheduleTable').getElementsByTagName('tbody')[0];
                scheduleTable.innerHTML = '';

                // Populate the table
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td contenteditable="true" class="${row.time === '12:20 PM - 01:20 PM' ? 'lunch-break' : ''}">${row.time}</td>
                    <td contenteditable="true" class="${row.monday === 'Lunch Break' ? 'lunch-break' : ''}">${row.monday || ''}</td>
                    <td contenteditable="true" class="${row.tuesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.tuesday || ''}</td>
                    <td contenteditable="true" class="${row.wednesday === 'Lunch Break' ? 'lunch-break' : ''}">${row.wednesday || ''}</td>
                    <td contenteditable="true" class="${row.thursday === 'Lunch Break' ? 'lunch-break' : ''}">${row.thursday || ''}</td>
                    <td contenteditable="true" class="${row.friday === 'Lunch Break' ? 'lunch-break' : ''}">${row.friday || ''}</td>
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
                document.getElementById('sectionHeader').innerText = `Room: ${classroomText}`;
                document.getElementById('scheduleTypeHeader').innerText = `${semesterText} ${typeShortText} Schedule`;

                updateLoadingProgress(100, 'Complete!');
                setTimeout(() => {
                    hideLoading();
                }, 300);
            })
            .catch(error => {
                console.error('Error fetching schedule:', error);
                hideLoading();
                showAlert('Error fetching schedule: ' + error.message, 'danger');
            });
    });


    // Fetch all classrooms with Loading
    document.addEventListener('DOMContentLoaded', function() {
        showLoading('Loading classrooms...');

        setTimeout(() => updateLoadingProgress(50, 'Fetching classroom data...'), 200);

        fetch('../../controllers/get-classrooms.php')
            .then(response => {
                updateLoadingProgress(80, 'Processing classrooms...');
                return response.json();
            })
            .then(data => {
                const enrollClassroom = document.getElementById('getClassroom');
                data.forEach(classroom => {
                    const option = document.createElement('option');
                    option.value = classroom.id;
                    option.innerText = classroom.room_name ?
                        `${classroom.department}, ${classroom.room_name} - ${classroom.type} (${classroom.room_number})` :
                        `${classroom.department}, ${classroom.type} (${classroom.room_number})`;
                    enrollClassroom.appendChild(option);
                });
                // Store classrooms for later use
                window.classrooms = data.reduce((acc, classroom) => {
                    acc[classroom.id] = classroom.type;
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
    // Update the print button event listener to capture edited content
    document.getElementById('printButton').addEventListener('click', function() {
        showLoading('Preparing PDF...');

        // Get the values from the form inputs
        const semester = document.getElementById('getSemester').value;
        const type = document.getElementById('getType').value;
        const classroom = document.getElementById('getClassroom').value;
        const classroomText = document.getElementById('getClassroom').options[document.getElementById('getClassroom').selectedIndex].text;
        const academicYear = document.getElementById('getAcademicYear').value;

        const preparedBy = document.getElementById('preparedBy').innerText;
        const reviewedBy = document.getElementById('reviewedBy').innerText;
        const approvedBy = document.getElementById('approvedBy').innerText;

        // Check if we have a schedule table
        const scheduleTable = document.getElementById('scheduleTable');
        if (!scheduleTable || scheduleTable.rows.length < 2) {
            hideLoading();
            showAlert('Please search for a schedule first before printing.', 'warning');
            return;
        }

        // Create a new data structure from the current table content
        const currentScheduleData = [];
        const rows = scheduleTable.rows;

        // Skip header row (index 0)
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].cells;
            currentScheduleData.push({
                time: cells[0].innerText.trim(),
                monday: cells[1].innerText.trim(),
                tuesday: cells[2].innerText.trim(),
                wednesday: cells[3].innerText.trim(),
                thursday: cells[4].innerText.trim(),
                friday: cells[5].innerText.trim()
            });
        }

        // Check if we have data
        if (currentScheduleData.length === 0) {
            hideLoading();
            showAlert('No schedule data found to print.', 'warning');
            return;
        }

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

        const fileName = `${classroomText} ${semesterText} ${typeText} Schedule SY ${academicYear}.pdf`;

        // Simulate progress updates
        setTimeout(() => updateLoadingProgress(30, 'Processing schedule data...'), 200);
        setTimeout(() => updateLoadingProgress(60, 'Creating PDF layout...'), 500);

        // Create PDF with custom layout
        const pdf = new jspdf.jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4',
        });

        // PDF dimensions and setup
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 10;
        const contentWidth = pageWidth - (margin * 2);
        const contentHeight = pageHeight - (margin * 2);

        let currentY = margin;

        function checkNewPage(requiredHeight) {
            if (currentY + requiredHeight > pageHeight - margin) {
                pdf.addPage();
                currentY = margin;
            }
        }

        // Function to process cell content with HTML tags
        function processCellContent(content) {
            if (!content) return '';

            // Replace <br> with newlines
            let processed = content.replace(/<br\s*\/?>/gi, '\n');

            // Replace <hr> with dashed line
            processed = processed.replace(/<hr[^>]*>/gi, '\n--------------------------------\n');

            // Remove any remaining HTML tags
            processed = processed.replace(/<[^>]*>/g, '');

            return processed.trim();
        }

        try {
            // Title section
            pdf.setFontSize(16);
            pdf.setFont('helvetica', 'bold');

            let typeShortText = '';
            if (type === 'none') typeShortText = 'Class';
            else if (type === 'prelim') typeShortText = 'Prelims';
            else if (type === 'midterm') typeShortText = 'Midterms';
            else if (type === 'final') typeShortText = 'Finals';

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

            // Room info
            pdf.setFontSize(10);
            const roomInfo = `Room: ${classroomText}`;
            const roomInfoWidth = pdf.getTextWidth(roomInfo);
            pdf.text(roomInfo, (pageWidth - roomInfoWidth) / 2, currentY);
            currentY += 10;

            // Table setup
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            const timeColWidth = 28;
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

            // Table rows using the current table data
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(8);

            for (let rowIndex = 0; rowIndex < currentScheduleData.length; rowIndex++) {
                const row = currentScheduleData[rowIndex];
                const dayContents = [
                    processCellContent(row.monday),
                    processCellContent(row.tuesday),
                    processCellContent(row.wednesday),
                    processCellContent(row.thursday),
                    processCellContent(row.friday)
                ];

                // Calculate row height based on content
                let maxLines = 1;
                for (let content of dayContents) {
                    if (content) {
                        const lines = content.split('\n');
                        maxLines = Math.max(maxLines, lines.length);
                    }
                }

                const rowHeight = Math.max(8, maxLines * 4 + 3);

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
                    pdf.setFillColor(255, 204, 203);
                    pdf.rect(margin, rowStartY, contentWidth, rowHeight, 'F');
                }

                // Draw row borders
                pdf.setDrawColor(0, 0, 0);
                pdf.setLineWidth(0.1);

                // Time column
                pdf.rect(margin, rowStartY, timeColWidth, rowHeight);
                const timeLines = pdf.splitTextToSize(row.time, timeColWidth - 6);
                let textY = rowStartY + 4;
                pdf.setFont('helvetica', 'bold');
                for (let line of timeLines) {
                    pdf.text(line, margin + 3, textY);
                    textY += 4;
                }

                // Day columns
                for (let i = 0; i < days.length; i++) {
                    const x = margin + timeColWidth + (i * dayColWidth);
                    const content = dayContents[i];

                    pdf.rect(x, rowStartY, dayColWidth, rowHeight);

                    if (content && content !== 'Lunch Break') {
                        const lines = content.split('\n');
                        textY = rowStartY + 4;

                        for (let line of lines) {
                            // Skip empty lines (except for dashed lines)
                            if (line.trim() === '' && !line.includes('-')) continue;

                            // Apply appropriate styling based on content
                            if (line.toLowerCase().includes('online')) {
                                pdf.setFont('helvetica', 'bold');
                            } else if (line.toLowerCase().includes('lab')) {
                                pdf.setFont('helvetica', 'italic');
                            } else if (line.toLowerCase().includes('pe') || line.toLowerCase().includes('physical education')) {
                                pdf.setFont('helvetica', 'bold');
                            } else {
                                pdf.setFont('helvetica', 'normal');
                            }

                            // Handle dashed lines (from <hr>)
                            if (line.includes('--------------------------------')) {
                                pdf.setDrawColor(150, 150, 150);
                                pdf.setLineWidth(0.2);
                                pdf.line(x + 3, textY - 1, x + dayColWidth - 3, textY - 1);
                                pdf.setDrawColor(0, 0, 0);
                                pdf.setLineWidth(0.1);
                            } else {
                                pdf.text(line, x + 3, textY);
                            }

                            textY += 4;
                        }
                    } else if (content === 'Lunch Break') {
                        pdf.setFont('helvetica', 'bold');
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
        const classroom = document.getElementById('getClassroom').value;
        const classroomText = document.getElementById('getClassroom').options[document.getElementById('getClassroom').selectedIndex].text;
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

        const fileName = `${classroomText} ${semesterText} ${typeText} Schedule SY ${academicYear}.png`;

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