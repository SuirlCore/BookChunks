
<?php 
    session_start();
	include 'pdo.php';

	unset($_SESSION['user_name']);
    unset($_SESSION['password']);
	
	//When form posted, perform this code
    if(isset($_POST['user_name'])){
			
		//save the post variables to a local variable
		$userName = $_POST['user_name'];
		
		//set the user name variable
		$_SESSION['user_name'] = $_POST['user_name'];

		//ask the database for the password for this username
        $sql = "SELECT pass FROM users WHERE userName = '". $userName. "';";
        $result = mysqli_query($pdo, $sql);

        //if no username found then send the user to addUser.php
        if(mysqli_num_rows($result) == 0){
            header(header: 'location: addUser.php');
        }

        //if the password matches
        if($result == $_POST['password']){
            //send the user off to scrollview.php
            header('location: scrollview.php');
        }else{
            echo 'Username or password is invalid. Please try again, or register a new user <br>';
        }

        return;
	}
?>

<!DOCTYPE html> 
<html>
<title>Book Chunks - Website Login</title> 
<head>
</head> 
<body>
    <form name="loginForm" action="login.php" method="POST">
		<h3>Enter Your User Name:</h3>
		<input type="text" name="user_name" value="">
		<br>
        <h3>Enter Your Password:</h3>
        <input type="text" name="password" value="">
        <br>
        <input type="submit" name="Submit" id="Submit" value="Submit">
        
    </form>
</body> 
</html>