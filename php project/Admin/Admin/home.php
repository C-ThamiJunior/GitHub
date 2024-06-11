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
		<link rel="stylesheet" href="styles.css">

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
				<!--  -->
				<!-- <li>
				<a href="#">
					<i class='bx bxs-doughnut-chart' ></i>
					<span class="text">Analytics</span>
				</a>
			</li> -->
				<li>
					<a href="transaction.php">
						<i class='bx bxs-message-dots'></i>
						<span class="text">Transaction</span>
					</a>
				</li>
				<li>
					<a href="add_user.php">
						<i class='bx bxs-message-dots'></i>
						<span class="text">verify Customer</span>
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
				<!-- <li>
				<a href="#">
					<i class='bx bxs-cog' ></i>
					<span class="text">Settings</span>
				</a>
			</li> -->
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
				<!-- <a href="#" class="nav-link"></a> -->
				<form action="#">
					<!-- <div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div> -->
				</form>
				<input type="checkbox" id="switch-mode" hidden>
				<label for="switch-mode" class="switch-mode"></label>
				<!-- <a href="#" class="notification">
				<i class='bx bxs-bell' ></i>
				<span class="num">8</span>
			</a> -->
				<a href="#" class="profile">
					<img src="img/people.png">
				</a>
			</nav>
			<!-- NAVBAR -->

			<!-- MAIN -->
			<main>
				<div class="head-title">
					<div class="left">
						<h1>Dashboard</h1>
						<ul class="breadcrumb">
							<li>
								<a href="#">Dashboard</a>
							</li>
							<li><i class='bx bx-chevron-right'></i></li>
							<li>
								<a class="active" href="#">Home</a>
							</li>
						</ul>
					</div>

				</div>

				<ul class="box-info">
					<li>
						<i class='bx bxs-group'></i>

						<span class="text">
							<?php
							$sql = "SELECT COUNT(*) AS total FROM transaction";
							$result = mysqli_query($conn, $sql);

							// Check if the query executed successfully
							if ($result) {
								$row = mysqli_fetch_assoc($result);
								$total_rows = $row['total'];
								echo "<h3>$total_rows</h3>";
							} else {
								echo " ";
							}

							?>
							<p>Total Transactions</p>
						</span>
					</li>
					<li>
						<i class='bx bxs-calendar-check'></i>
						<span class="text">
							<?php
							// fix this shit
							$sql = "SELECT COUNT(*) AS total FROM transaction where current_date LIKE '%2024-05-22%' ";
							$result = mysqli_query($conn, $sql);

							// Check if the query executed successfully
							if ($result) {
								$row = mysqli_fetch_assoc($result);
								$total_rows = $row['total'];
								echo "<h3>$total_rows</h3>";
							} else {
								echo " ";
							}

							?>
							<p>Latest Transactions</p>
						</span>
					</li>
					<!-- <li>
					<i class='bx bxs-dollar-circle' ></i>
					<span class="text">
						<h3></h3>
						<p></p>
					</span>
				</li> -->
				</ul>

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