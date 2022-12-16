<?php
session_start();
include('../layout/header.php');
if ($this_user['id_role'] != 1) {
    header('location:file.php');
}
$s_keyword = "";
if (isset($_POST['search'])) {
    $s_keyword = $_POST['s_keyword'];
}
?>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="uploadModalLabel">Add User</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../controller/add_user.php" class="mx-4" method="post">
                    <div class="form-outline mb-3">
                        <label class="form-label" for="form1"><b>Username</b></label>
                        <input type="text" id="form1" class="form-control" name="username" />
                    </div>

                    <div class="form-outline mb-3">
                        <label class="form-label" for="form1"><b>Password</b></label>
                        <input type="text" id="form1" class="form-control" name="password" />
                    </div>

                    <div class="form-outline mb-3">
                        <label class="form-label" for="form1"><b>Name</b></label>
                        <input type="text" id="form1" class="form-control" name="name" />
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="form1"><b>Role</b></label>
                        <select class="form-select" name="role">
                            <option value="--" disabled selected></option>
                            <?php
                            foreach ($user->show_role() as $x) {
                                echo '<option value="' . $x['id_role'] . '">' . $x['role'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="row pt-3 border-top text-center">
                        <div class="col-12">
                            <input type="submit" class="btn btn-dark btn-lg px-5 w-100" value="Add">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editModal1" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editModalLabel">Edit User</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../controller/save_user.php" class="mx-4" method="post">
                    <input type="hidden" name="id" id="eid" value="">
                    <div class="form-outline mb-3">
                        <label class="form-label" for="eusername"><b>Username</b></label>
                        <input type="text" id="eusername" class="form-control" name="username" />
                    </div>

                    <div class="form-outline mb-3">
                        <label class="form-label" for="epassword"><b>Password</b></label>
                        <input type="text" id="epassword" class="form-control" name="password" />
                    </div>

                    <div class="form-outline mb-3">
                        <label class="form-label" for="ename"><b>Name</b></label>
                        <input type="text" id="ename" class="form-control" name="name" />
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="role"><b>Role</b></label>
                        <select class="form-select" name="role">
                            <option id="erole" value="" selected></option>
                            <option id="nrole" value=""></option>
                        </select>
                    </div>
                    <div class="row pt-3 border-top text-center">
                        <div class="col-12">
                            <input type="submit" class="btn btn-lg btn-dark px-5 w-100" value="Save">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row mt-5">
    <div class="col-6 mt-1"><button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#uploadModal">
            Add User
        </button></div>
    <div class="col-6 mt-1 d-flex justify-content-end">
        <form method="POST" action="">
            <div class="row d-flex justify-content-end">
                <div class="col-sm-8 d-flex justify-content-end pe-0">
                    <div class="form-group">
                        <input type="text" placeholder="Keyword" name="s_keyword" id="s_keyword" class="form-control" value="<?php echo $s_keyword; ?>">
                    </div>
                </div>
                <div class="col-sm-4 d-flex justify-content-end ps-0">
                    <button id="search" name="search" class="btn btn-dark">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>
<table class="table mt-3">
    <thead class="table-dark">
        <tr>
            <th class="text-center">Username</th>
            <th class="text-center">Password</th>
            <th class="text-center">Name</th>
            <th class="text-center">Role</th>
            <th class="text-center">Actions</th>
        </tr>
    </thead>
    <?php
    $search_keyword = '%' . $s_keyword . '%';
    foreach ($user->search_user($search_keyword) as $x) {
    ?>
        <tbody>
            <tr>
                <td><?php echo $x['username']; ?></td>
                <td><?php echo $x['password']; ?></td>
                <td><?php echo $x['name']; ?></td>
                <td class="text-center"><?php echo $x['role']; ?></td>
                <td class="text-center">
                    <a class="text-center px-2 uedit" href="#" id="<?php echo $x['id']; ?>" data-bs-toggle="modal" data-bs-target="#editModal1" vare1="<?php echo $x['username']; ?>" vare2="<?php echo $x['password']; ?>" vare3="<?php echo $x['name']; ?>" vare4="<?php echo $x['id_role']; ?>" vare5="<?php echo $x['role']; ?>" onclick=editFunction($(this))>Edit</a>
                    <a class="text-center px-2 delete" href="../controller/delete_user.php?id=<?php echo $x['id']; ?>" onClick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>
        </tbody>
    <?php
    }
    ?>
</table>

<script type="text/javascript">
    function editFunction(eid) {
        var vare = eid.attr("id");
        var vare1 = eid.attr("vare1");
        var vare2 = eid.attr("vare2");
        var vare3 = eid.attr("vare3");
        var vare4 = eid.attr("vare4");
        var vare5 = eid.attr("vare5");
        if (vare4 == 1) {
            var vare6 = 2;
            var vare7 = "Admin";
        } else {
            var vare6 = 1;
            var vare7 = "Super Admin";
        }
        $("#eid").val(vare);
        $("#eusername").val(vare1);
        $("#epassword").val(vare2);
        $("#ename").val(vare3);
        $("#erole").val(vare4);
        $("#erole").html(vare5);
        $("#nrole").val(vare6);
        $("#nrole").html(vare7);
    }
</script>
<?php
include('../layout/footer.php');
?>