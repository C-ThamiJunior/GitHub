<?php
session_start();
include "db_conn.php";

if (isset($_POST['id_no']) && isset($_POST['pin'])) {
    $id_number = $_POST['id_no'];
    $pin = $_POST['pin'];

    if (empty($id_number) || empty($pin)) {
        header("Location: add_user.php?error=ID number and PIN are required");
        exit();
    } else {
        $sql = "SELECT * FROM bank WHERE account_no='$id_number'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $bank = $row['bank_name'];
                $account_no = $row['account_no'];
                $name = $row['first_name'];
                $surname = $row['last_name'];
                $email = $row['email'];
                $address = $row['address'];
                $phone = $row['phone'];
                $balance = $row['balance'];

                // Generate person_id
                $person_id = mt_rand(1000, 9999); // Assuming person_id should auto-increment...

                // Insert into person table
                $sql2 = "INSERT INTO person(person_id, first_name, last_name, email, address, phone) VALUES($person_id, '$name', '$surname', '$email', '$address', '$phone')";
                mysqli_query($conn, $sql2);

                // Insert into client table
                $sql3 = "INSERT INTO client(client_id, pin, person_id) VALUES($person_id, $pin, $person_id)";
                mysqli_query($conn, $sql3);

                // Insert into account table
                $sql4 = "INSERT INTO account(account_no, balance, client_id, pin) VALUES($account_no, $balance, $person_id, $pin)";
                mysqli_query($conn, $sql4);

                // Insert into ourbank table
                $sql5 = "INSERT INTO ourbank SELECT * FROM bank WHERE account_no='$id_number'";
                mysqli_query($conn, $sql5);
            }
            header("Location: users.php");
            exit();
        } else {
            header("Location: add_user.php?error=ID number invalid");
            exit();
        }
    }
} else {
    header("Location: index.php");
    exit();
}
?>
