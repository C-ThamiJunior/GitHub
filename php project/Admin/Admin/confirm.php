<?php
session_start();
include "db_conn.php";

if (isset($_POST['id_no'])) {

	$id_number = $_POST['id_no'];

	if (empty($id_number)) {
		header("Location: add_user.php?error=ID number is required");
		exit();
	} else {
		$sql = "SELECT * FROM bank WHERE person_id='$id_number'";

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

				$person_id = mt_rand(1000, 9999); // person_id should auto-increment...
				$pin = mt_rand(1000, 9999);
				$sql2 = "INSERT INTO person(person_id, first_name, last_name, email, address, phone) VALUES($person_id, '$name', '$surname', '$email', '$address', '$phone')";
				$sql3 = "INSERT INTO client(client_id, pin, person_id) VALUES($person_id, $pin, $person_id)";
				$sql4 = "INSERT INTO account(account_no, balance, client_id) VALUES($account_no, $balance, $person_id)";

				mysqli_query($conn, $sql2);
				mysqli_query($conn, $sql3);
				mysqli_query($conn, $sql4);
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
