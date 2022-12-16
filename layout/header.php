<!DOCTYPE html>
<html>
<?php
include('../model/user.php');
$user = new user();
$sql = "SELECT * FROM users WHERE id = '" . $_SESSION['user'] . "'";
$this_user = $user->details($sql);
?>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>File Sharing</title>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">File Sharing</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel">Welcome!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 px-3">
                        <?php
                        if (!isset($_SESSION['user']) || (trim($_SESSION['user']) == '')) {
                            echo '<h2 class="text-center">Nothing to see here...</h2>';
                        } else {
                        ?>
                            <li class="nav-item">
                                <div class="row border-top border-bottom">
                                    <div class="col-6 my-1 py-2 d-flex justify-content-center border-end">
                                        <a class="wtxt" href="../upload/file.php">Manage File</a>
                                    </div>
                                    <div class="col-6 my-1 py-2 d-flex justify-content-center border-start">
                                        <?php
                                        if ($this_user['id_role'] == 1) {
                                            echo '<a class="wtxt" href="../page/manage_user.php">Manage User</a>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </li>
                        <?php
                        }
                        ?>

                        <li class="nav-item mt-4 d-flex justify-content-center">
                            <?php
                            if (!isset($_SESSION['user']) || (trim($_SESSION['user']) == '')) {
                                echo '<a class="btn btn-light btn-lg px-5 mb-5 w-100" href="../index.php">Login</a>';
                            } else {
                                echo '<a class="btn btn-light btn-lg px-5 mb-5 w-100" href="../controller/logout.php">Logout</a>';
                            }
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>