<?php
include('../model/user.php');
$user = new user();
$user->add_user($_POST['username'], $_POST['password'], $_POST['name'], $_POST['role']);
header('location:../page/manage_user.php');
