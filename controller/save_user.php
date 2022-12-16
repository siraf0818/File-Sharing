<?php
include('../model/user.php');
$user = new user();
$user->edit_user($_POST['id'], $_POST['username'], $_POST['password'], $_POST['name'], $_POST['role']);
header('location:../page/manage_user.php');
