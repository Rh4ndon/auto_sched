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

                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">



                            <!-- [ schedule table ] start -->

                            <style>
                                th,
                                td {
                                    text-align: center;
                                    vertical-align: middle;
                                }

                                .time-column {
                                    width: 10%;
                                }

                                .day-column {
                                    width: 18%;
                                }
                            </style>


                            <div class="container mt-4 bg-white p-4">
                                <h3 class="text-center">CLASS SCHEDULE</h3>
                                <h5 class="text-center">Second Semester, SY: 2024 - 2025</h5>
                                <h6 class="text-center">BSEd – 1</h6>

                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="time-column">TIME</th>
                                            <th class="day-column">MONDAY</th>
                                            <th class="day-column">TUESDAY</th>
                                            <th class="day-column">WEDNESDAY</th>
                                            <th class="day-column">THURSDAY</th>
                                            <th class="day-column">FRIDAY</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>7:00 - 8:00</td>
                                            <td></td>
                                            <td>GEC 8<br> Z. Gongob<br> Room 1</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>8:00 - 9:00</td>
                                            <td>GE Elect SEd 3<br>Dr. K. Gonzales<br>Laboratory 2</td>
                                            <td>GE Elect SEd 3<br>Dr. K. Gonzales<br>Laboratory 2</td>
                                            <td>GE Elect SEd 3<br>Dr. K. Gonzales<br>Laboratory 2</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>9:00 - 10:00</td>
                                            <td colspan="5"></td>
                                        </tr>
                                        <tr>
                                            <td>10:00 - 11:00</td>
                                            <td></td>
                                            <td>GE Elect SEd 2<br>R. Agron<br>Room 16</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>11:00 - 12:00</td>
                                            <td colspan="5"></td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td colspan="6">LUNCH BREAK</td>
                                        </tr>
                                        <tr>
                                            <td>1:00 - 2:00</td>
                                            <td>GEC 6<br> M. Velasco<br> Room 1</td>
                                            <td>GEC 4<br> O. Saguibo<br> Room 16</td>
                                            <td></td>
                                            <td>GEC 4<br> O. Saguibo<br> Room 16</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>2:00 - 3:00</td>
                                            <td></td>
                                            <td>GEC 5<br> J. Delos Santos<br> Room 17</td>
                                            <td></td>
                                            <td>GE Elect SEd 2<br> R. Agron<br> Room 16</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>3:00 - 4:00</td>
                                            <td>GEC 5<br> J. Delos Santos<br> Room 2</td>
                                            <td></td>
                                            <td></td>
                                            <td>PE 2<br> J. Foster<br> Gym</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>4:00 - 5:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>GEC 8<br> Z. Gongob<br> ELX Room</td>
                                            <td>GEC 6<br> M. Velasco<br> Room 1</td>
                                        </tr>
                                    </tbody>
                                </table>


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