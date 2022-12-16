<?php
include_once('DbConnection.php');

class User extends DbConnection
{

    public function __construct()
    {
        parent::__construct();
    }

    public function check_login($username, $password)
    {
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $query = $this->connection->query($sql);

        if ($query->num_rows > 0) {
            $row = $query->fetch_array();
            return $row['id'];
        } else {
            return false;
        }
    }

    public function details($sql)
    {

        $query = $this->connection->query($sql);

        $row = $query->fetch_array();

        return $row;
    }

    public function show_role()
    {
        $query = $this->connection->query("select * from roles");
        while ($row = $query->fetch_array()) {
            $role[] = $row;
        }
        return $role;
    }

    public function show_info()
    {
        $query = $this->connection->query("select a.*, b.* from users a INNER JOIN roles b on a.id_role = b.id_role");
        while ($row = $query->fetch_array()) {
            $info[] = $row;
        }
        return $info;
    }

    public function escape_string($value)
    {
        return $this->connection->real_escape_string($value);
    }

    function get_id($id)
    {
        $query = $this->connection->query("select a.*, b.* from users a INNER JOIN roles b on a.id_role = b.id_role WHERE a.id='$id'");
        while ($row = $query->fetch_array()) {
            $info[] = $row;
        }
        return $info;
    }

    public function add_user($username, $password, $name, $role)
    {
        $this->connection->query("insert into users values ('','$username','$password','$name','$role')");
    }

    function edit_user($id, $username, $password, $name, $role)
    {
        mysqli_query($this->connection, "UPDATE users set username = '$username',password='$password',name='$name', id_role='$role' where id='$id'");
    }

    function delete_user($id)
    {
        mysqli_query($this->connection, "DELETE from users where id = '$id'");
    }

    function search_user($search_keyword)
    {
        $query = $this->connection->prepare("SELECT a.*, b.* from users a INNER JOIN roles b on a.id_role = b.id_role WHERE username LIKE ? OR password LIKE ? OR name LIKE ? OR role LIKE ?");
        $query->bind_param('ssss', $search_keyword, $search_keyword, $search_keyword, $search_keyword);
        $query->execute();
        while ($row = $query->get_result()) {
            $info = $row;
        }
        return $info;
    }
}
