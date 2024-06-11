<?php
session_start();
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
	$id = $_POST['person_id'];

	// Check if the id is valid
	if (!empty($id) && ctype_digit($id)) {
		// Prepare a delete statement
		// $person_id = mt_rand(1000, 9999); // person_id should auto-increment...
		// $pin = mt_rand(1000, 9999);
		// maybe truncate stuff
		// delete from transaction too;


		$subSql = "SELECT account_no FROM account where client_id = $id";
		$result = mysqli_query($conn, $subSql);
		$feedback = mysqli_fetch_assoc($result);
		$acc = $feedback['account_no'];

		$sql = "DELETE FROM ourbank WHERE account_no = $acc";
		$sql2 = "DELETE FROM transaction WHERE account_no = (SELECT account_no FROM account where client_id = $id)";
		$sql3 = "DELETE FROM person WHERE person_id = $id";
		$sql4 = "DELETE FROM client WHERE person_id = $id";
		$sql5 = "DELETE FROM account WHERE client_id = $id";


		if (!mysqli_query($conn, $sql)) {
			echo "Error executing query: " . mysqli_error($conn);
		}

		if (!mysqli_query($conn, $sql2)) {
			echo "Error executing query: " . mysqli_error($conn);
		}

		if (!mysqli_query($conn, $sql5)) {
			echo "Error executing query: " . mysqli_error($conn);
		}

		if (!mysqli_query($conn, $sql4)) {
			echo "Error executing query: " . mysqli_error($conn);
		}

		if (!mysqli_query($conn, $sql3)) {
			echo "Error executing query: " . mysqli_error($conn);
		}




		// check errors from all queries, else try truncate delete...
	} else {
		echo "Invalid ID";
	}
}

if (isset($_SESSION['person_id']) && isset($_SESSION['admin_id'])) {

?>

	<?php



	// Close the database connection

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
					<div class="form-input">
						<input type="search" placeholder="Search...">
						<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
					</div>
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
						<h1>Users</h1>
						<ul class="breadcrumb">
							<li>
								<a href="#">Users</a>
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
							<h3>Users</h3>
							<i class='bx bx-search'></i>
							<i class='bx bx-filter'></i>
						</div>

						<table>
							<thead>
								<tr>

									<th>Full Names</th>
									<th>contact</th>
									<th>Account Number</th>
									<th>Amount</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$sql = "SELECT * FROM account, person WHERE person.person_id = account.client_id";
								$result = mysqli_query($conn, $sql);

								if ($result && mysqli_num_rows($result) > 0) {
									while ($row = mysqli_fetch_assoc($result)) {
								?>
										<tr>
											<td>
												<!-- <img src="img/people.png"> -->
												<p><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></p>
											</td>
											<td>
												<?php echo $row['phone']; ?>
											</td>
											<td>
												<?php echo $row['account_no']; ?>
											</td>
											<td>
												<?php echo 'R' . $row['balance']; ?>
											</td>
											<td>
												<form method="post" action="">
													<input type="hidden" name="person_id" value="<?php echo htmlspecialchars($row['person_id']); ?>" />
													<input type="submit" name="delete" value="Delete" />
												</form>
												<!-- <form method="post" action="edit_client.php">
										<input type="hidden" name="id" value="<?php echo htmlspecialchars($row['person_id']); ?>" />
										<input type="submit" name="edit" value="Edit" />
									</form> -->
											</td>
										</tr>
								<?php
									}
								} else {
									echo "No data found.";
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
		<div id="popupForm" class="popup-form">
			<div class="form-container">
				<span class="close-btn" id="closeForm">&times;</span>
				<h2>Add New User</h2>
				<form id="newUserForm">
					<label for="name">Name:</label>
					<input type="text" id="name" name="name" required><br><br>
					<label for="surname">Surname:</label>
					<input type="text" id="surname" name="surname" required><br><br>
					<label for="email">Email:</label>
					<input type="email" id="email" name="email" required><br><br>
					<label for="account_no">Account No:</label>
					<input type="text" id="account_no" name="account_no" required><br><br>
					<button type="submit">Submit</button>
				</form>
			</div>
		</div>

		<script>
			// Get the button, form, and close elements
			var addUserButton = document.getElementById("addUserButton");
			var popupForm = document.getElementById("popupForm");
			var closeForm = document.getElementById("closeForm");

			// Show the form when the button is clicked
			addUserButton.onclick = function() {
				popupForm.style.display = "block";
			}

			// Close the form when the close button is clicked
			closeForm.onclick = function() {
				popupForm.style.display = "none";
			}

			// Close the form when clicking outside of the form
			window.onclick = function(event) {
				if (event.target == popupForm) {
					popupForm.style.display = "none";
				}
			}

			// Handle form submission
			document.getElementById("newUserForm").onsubmit = function(event) {
				event.preventDefault();
				// Add form submission logic here
				console.log("Name:", document.getElementById("name").value);
				console.log("Surname:", document.getElementById("surname").value);
				console.log("Email:", document.getElementById("email").value);
				console.log("Account No:", document.getElementById("account_no").value);
				// Hide the form after submission
				popupForm.style.display = "none";
			}
		</script>

	</body>
	</body>

	</html>

<?php
} else {
	header("Location: index.php");
	exit();
}
?>