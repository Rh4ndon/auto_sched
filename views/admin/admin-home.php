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
                                            <h5 class="m-b-10">Admin-Dashboard</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="admin-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">

                            <!-- [ static-layout ] start -->
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Website's Data</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Students</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p id="total_students" style="font-size: 1.5em;"><i class="feather icon-users"></i> Total Students: Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Teachers</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p id="total_teachers" style="font-size: 1.5em;"><i class="feather icon-user"></i> Total Teachers: Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Subjects</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p id="total_subjects" style="font-size: 1.5em;"><i class="feather icon-book"></i> Total Subjects: Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Sections</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p id="total_sections" style="font-size: 1.5em;"><i class="feather icon-layers"></i> Total Sections: Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Enrollments</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p id="total_enrollments" style="font-size: 1.5em;"><i class="feather icon-check-square"></i> Total Enrollments: Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Classrooms</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p id="total_classrooms" style="font-size: 1.5em;"><i class="feather icon-home"></i> Total Classrooms: Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                fetch('../../controllers/get-dashboard-data.php')
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        document.getElementById('total_students').innerHTML = '<i class="feather icon-users"></i> Total Students: ' + data.total_students;
                                                        document.getElementById('total_teachers').innerHTML = '<i class="feather icon-user"></i> Total Teachers: ' + data.total_teachers;
                                                        document.getElementById('total_subjects').innerHTML = '<i class="feather icon-book"></i> Total Subjects: ' + data.total_subjects;
                                                        document.getElementById('total_sections').innerHTML = '<i class="feather icon-layers"></i> Total Sections: ' + data.total_sections;
                                                        document.getElementById('total_enrollments').innerHTML = '<i class="feather icon-check-square"></i> Total Enrollments: ' + data.total_enrollments;
                                                        document.getElementById('total_classrooms').innerHTML = '<i class="feather icon-home"></i> Total Classrooms: ' + data.total_classrooms;
                                                    })
                                                    .catch(error => console.error('Error fetching dashboard data:', error));
                                            });
                                        </script>

                                    </div>
                                </div>
                                <!-- [ static-layout ] end -->
                            </div>
                            <!-- [ Main Content ] end -->


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <?php @include 'footer.php'; ?>