<?php @include 'header.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    .catch .pcoded-main-container {
        margin-left: -30px !important;
        max-width: 100% !important;
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
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-12">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10">Teacher-Profile</h5>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="teacher-home.php"><i class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="teacher-profile.php">Profile</a></li>

                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->
                        <!-- [ Main Content ] start -->
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Edit Profile <i class="feather icon-edit"></i> </h5>
                                    </div>
                                    <div class="card-body">

                                        <form method="POST" action="../../controllers/user-update.php">
                                            <input type="hidden" name="id" value="<?php echo $_SESSION['id']; ?>">
                                            <input type="hidden" name="user_type" value="teacher">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input type="text" name="name" class="form-control" id="name" placeholder="Enter Name" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" name="email" class="form-control" id="email" placeholder="Enter Email" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="gender">Gender</label>
                                                <select name="gender" class="form-control" id="gender">
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="password">Password</label>
                                                <input type="password" name="password" class="form-control" id="password" placeholder="Enter Password" required>
                                            </div>


                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                        </form>

                                    </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../../controllers/get-edit.php?id=<?php echo $_SESSION['id']; ?>')
            .then(response => response.json())
            .then(data => {
                console.log(data);
                const editName = document.getElementById('name');
                const editEmail = document.getElementById('email');
                const editGender = document.getElementById('gender');
                const editPassword = document.getElementById('password');


                editName.value = data[0].name;
                editEmail.value = data[0].email;
                editGender.value = data[0].gender;


                // Store users for later use
                window.users = data.reduce((acc, user) => {
                    acc[user.id] = user.user_name;
                    return acc;
                }, {});
            })
            .catch(error => console.error('Error fetching users:', error));
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
            <strong>${type === 'success' ? 'Success' : 'Error'}!</strong> ${message}
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