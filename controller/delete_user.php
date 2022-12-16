<?php
include('../model/user.php');
$user = new user();
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $info = $user->get_id($id);
    $this_user = $info[0]['id'];
    $user->delete_user($id);
    header('Location: ../page/manage_user.php');
} else {
    header('Location: ../page/manage_user.php');
}
