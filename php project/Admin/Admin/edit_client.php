<?php 
session_start();
include "db_conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];

    
?>

<!DOCTYPE html>
<html>
<head>
	<title>LOGIN</title>
	<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
	<style>
        body {
	background-image: url('./img/pp.jpg');
	display: flex;
	justify-content: center;
	align-items: center;
	height: 100vh;
	flex-direction: column;
}

*{
	font-family: sans-serif;
	box-sizing: border-box;
}

form {
	width: 500px;
	border: 2px solid #ccc;
	padding: 30px;
	background: #fff;
	border-radius: 15px;
}

h2 {
	text-align: center;
	margin-bottom: 40px;
}

input {
	display: block;
	border: 2px solid #ccc;
	width: 95%;
	padding: 10px;
	margin: 10px auto;
	border-radius: 5px;
}
label {
	color: #000000;
	font-size: 18px;
	padding: 10px;
}

button {
	float: right;
	background: #5b0303;
	padding: 10px 15px;
	color: #fff;
	border-radius: 5px;
	margin-right: 10px;
	border: none;
}
button:hover{
	opacity: .7;
}
.error {
   background: #F2DEDE;
   color: #A94442;
   padding: 10px;
   width: 95%;
   border-radius: 5px;
   margin: 20px auto;
}

h1 {
	text-align: center;
	color: #fff;
}

a {
	float: right;
	background: #555;
	padding: 10px 15px;
	color: #fff;
	border-radius: 5px;
	margin-right: 10px;
	border: none;
	text-decoration: none;
}
a:hover{
	opacity: .7;
}
    </style>
</head>
<body>
     <form action="login.php" method="post">
     	<h2>Edit Client Details</h2>
     	<?php if (isset($_GET['error'])) { ?>
     		<p class="error"><?php echo $_GET['error']; ?></p>
     	<?php } ?>
        <?php 
$sql = "SELECT * FROM client_account WHERE id='$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);

    $bank = htmlspecialchars($row['bank_name']);
    $account_no = htmlspecialchars($row['account_no']);
    $pin = htmlspecialchars($row['pin']);
    $name = htmlspecialchars($row['name']);
    $surname = htmlspecialchars($row['surname']);
    $amount = htmlspecialchars($row['amount']);
?>
    <form method="post" action="">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        
        <label>Name </label>
        <input type="text" name="name" value="<?php echo $name; ?>"><br>
        
        <label>Surname</label>
        <input type="text" name="surname" value="<?php echo $surname; ?>"><br>
        
        <label>Pin</label>
        <input type="number" name="pin" value="<?php echo $pin; ?>"><br>
        
        <label>Bank Name</label>
        <input type="text" name="bank" value="<?php echo $bank; ?>"><br>
        
        <label>Account Number</label>
        <input type="number" name="account_no" value="<?php echo $account_no; ?>"><br>
        
        <label>Amount</label>
        <input type="text" name="amount" value="<?php echo $amount; ?>"><br>
        
        <button type="submit" name="edit">Edit</button>
    </form>
<?php
} else {
    echo "Record not found.";
}
?>
</body>
</html>



<?php 
}else{
     header("Location: index.php");
     exit();
}
 ?>