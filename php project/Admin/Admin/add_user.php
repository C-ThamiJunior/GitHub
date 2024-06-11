<?php
session_start();
include "db_conn.php";
if (isset($_SESSION['person_id']) && isset($_SESSION['admin_id'])) {

?>

	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<!-- Boxicons -->
		<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
		<!-- My CSS -->
		<link rel="stylesheet" href="styless.css">

		<title>Admin Panel</title>
	</head>

	<body>


		<!-- SIDEBAR -->
		<section id="sidebar">
			<a href="home.php" class="brand">
				<i class='bx bxs-smile'></i>
				<span class="text">Admin Panel</span>
			</a>
			<ul class="side-menu top">
				<li class="active">
					<a href="home.php">
						<i class='bx bxs-dashboard'></i>
						<span class="text">Dashboard</span>
					</a>
				</li>
				<li>
					<a href="transaction.php">
						<i class='bx bxs-message-dots'></i>
						<span class="text">Transaction</span>
					</a>
				</li>
				<li>
					<a href="add_user.php">
						<i class='bx bxs-message-dots'></i>
						<span class="text">verify customer</span>
					</a>
				</li>
				<li>
					<a href="users.php">
						<i class='bx bxs-message-dots'></i>
						<span class="text">Clients</span>
					</a>
				</li>

			</ul>
			<ul class="side-menu">
				<li>
					<a href="logout.php" class="logout">
						<i class='bx bxs-log-out-circle'></i>
						<span class="text">Logout</span>
					</a>
				</li>
			</ul>
		</section>
		<!-- SIDEBAR -->



		<!-- CONTENT -->
		<section id="content">
			<!-- NAVBAR -->
			<nav>
				<i class='bx bx-menu'></i>
				<form action="#">
					<div class="form-input">
						<input type="search" placeholder="Search...">
						<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
					</div>
				</form>
				<input type="checkbox" id="switch-mode" hidden>
				<label for="switch-mode" class="switch-mode"></label>
				<a href="#" class="profile">
					<img src="img/people.png">
				</a>
			</nav>
			<!-- NAVBAR -->

			<!-- MAIN -->
			<main>
				<div class="head-title">
					<div class="left">
						<h1>customer verification</h1>
						<ul class="breadcrumb">
							<li>
								<a href="#">Clients</a>
							</li>
							<li><i class='bx bx-chevron-right'></i></li>
							<li>
								<a class="active" href="#">Home</a>
							</li>
						</ul>
					</div>

				</div>

				<div class="table-data">
					<div class="order">
						<div class="head">
							<h3>Some Customer Details</h3>
							<i class='bx bx-search'></i>
							<i class='bx bx-filter'></i>
						</div>
						<form action="confirm.php" method="post">
							<h2>Confirm client details</h2>
							<?php if (isset($_GET['error'])) { ?>
								<p class="error"><?php echo $_GET['error']; ?></p>
							<?php } ?>
							<label>ID Number</label>
							<input type="number" name="id_no" placeholder=""><br>

							<button type="submit">Confirm</button>
						</form>

						<table>
							<thead>
								<tr>
									<th>Bank Name</th>
									<th>Account No</th>
									<th>First Name</th>
									<th>Last Name</th>
									<th>Email</th>
									<th>Address</th>
									<th>Phone</th>
									<th>Balance</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (isset($_POST['id_no'])) {
									$id_number = $_POST['id_no'];
									$sql = "SELECT * FROM bank WHERE person_id='$id_number'";
									$result = mysqli_query($conn, $sql);
									if ($result && mysqli_num_rows($result) > 0) {
										while ($row = mysqli_fetch_assoc($result)) {
								?>
											<tr>
												<td><?php echo $row['bank_name']; ?></td>
												<td><?php echo $row['account_no']; ?></td>
												<td><?php echo $row['first_name']; ?></td>
												<td><?php echo $row['last_name']; ?></td>
												<td><?php echo $row['email']; ?></td>
												<td><?php echo $row['address']; ?></td>
												<td><?php echo $row['phone']; ?></td>
												<td><?php echo $row['balance']; ?></td>
											</tr>
								<?php
										}
									} else {
										echo "<tr><td colspan='8'>No data found.</td></tr>";
									}
								}
								?>
							</tbody>
						</table>

					</div>


			</main>
			<!-- MAIN -->
		</section>
		<!-- CONTENT -->


		<script src="script.js"></script>
	</body>

	</html>

<?php
} else {
	header("Location: index.php");
	exit();
}
?>
