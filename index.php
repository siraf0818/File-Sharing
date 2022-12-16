<?php
session_start();
if (isset($_SESSION['user'])) {
    header('location:file.php');
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Website File Sharing</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h2 class="text-center text-dark mt-5">File Sharing</h2>
                <div class="card my-4">
                    <form class="card-body cardbody-color p-lg-5" method="POST" action="controller/login.php">
                        <div class="text-center">
                            <img src="assets/img/logo.webp" class="img-fluid profile-image-pic img-thumbnail rounded-circle my-3" width="200px" alt="profile">
                        </div>
                        <fieldset>
                            <div class="form-group mb-3">
                                <input class="form-control" placeholder="Username" type="text" name="username" autofocus required>
                            </div>
                            <div class="form-group mb-3">
                                <input class="form-control" placeholder="Password" type="password" name="password" required>
                            </div>
                            <div class="text-center"><button type="submit" name="login" class="btn btn-lg btn-dark px-5 mb-5 w-100"> Login</button>
                            </div>
                            <div class="form-text text-center mb-5 text-dark">Not
                                Admin? <a href="upload/file.php" class="text-dark fw-bold"> Login as Guest</a>
                            </div>
                            <?php
                            if (isset($_SESSION['message'])) {
                            ?>
                                <div class="alert alert-dark text-center">
                                    <?php echo $_SESSION['message']; ?>
                                </div>
                            <?php
                                unset($_SESSION['message']);
                            }
                            ?>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>