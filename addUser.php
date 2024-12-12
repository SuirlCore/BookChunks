<?php 
    session_start();
	include 'pdo.php';

    $nFlag = 0;
    $pFlag = 0;
    $fnFlag = 0;
    $lnFlag = 0;
    $eFlag = 0;

    if (isset($_POST['newUser'])) {
        //check to see if the username is already in use
        $sql = "SELECT userName FROM users WHERE userName = '". $userName. "';";
        $result = mysqli_query($pdo, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $nFlag = "1";
        }else{
            echo "This username is taken, please choose another one.";
        }
    }else{
        echo"Please enter a user name. <br>";
    }

    if (isset($_POST["newPassword"])) {
        $pFlag = "1";
    }else{
        echo "Please enter a password. <br>";
    }
    
    if (isset($_POST['fName'])) {
        $fnFlag = "1";
    }else{
        echo"Please enter a first name. <br>";
    }
    
    if (isset($_POST['lName'])) {
        $lnFlag = "1";
    }else{
        echo"Please enter a last name. <br>";
    }

    if (isset($_POST['email'])) {
        $eFlag = "1";
    }else{
        echo"Please enter an email. <br>";
    }

    //if all of the fields are set and unique then add a new record to the database
    if (($nFlag + $pFlag + $fnFlag + $lnFlag + $eFlag) == 5){
        $sql = "INSERT INTO users (userName, pass, realFirstName, realLastName, email) VALUES ('". $_POST['newUser']. ", '". $_POST["newPassword"]. "', '". $_POST['fName']. "', '". $_POST['lName']. "', '". $_POST['email']. "');";
        mysqli_query($pdo, $sql);
    }

?>

<!DOCTYPE html> 
<html>
<title>Book Chunks - Register a new user</title> 
<head>
</head> 
<body>
    <form name="loginForm" action="addUser.php" method="POST">
		<h3>Enter a new User Name:</h3>
		<input type="text" name="newUser" value="">
		<br>
        <h3>Enter a Password:</h3>
        <input type="text" name="newPassword" value="">
        <br>
        <h3>Enter your First Name:</h3>
        <input type="text" name="fName" value="">
        <br>
        <h3>Enter your Last Name:</h3>
        <input type="text" name="lName" value="">
        <br>
        <h3>Enter your Email:</h3>
        <input type="text" name="email" value="">
        <br>
        <input type="submit" name="Submit" id="Submit" value="Submit">
        
    </form>
</body> 
</html>