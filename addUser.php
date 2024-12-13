<?php 
    session_start();
	include 'pdo.php';

    $nFlag = 0;
    $pFlag = 0;
    $fnFlag = 0;
    $lnFlag = 0;
    $eFlag = 0;

    if (isset($_POST['newUser'])) {
        $userName = $_POST['newUser'];

        //check to see if the username is already in use
        //$sql = "SELECT userName FROM users WHERE userName = '". $userName. "';";
        //$run = mysqli_query($pdo, $sql);
        
        //echo strval($run);
        $nFlag = 1;
        $newUser = $_POST['newUser'];
        unset($_POST['newUser']);
    }else{
        echo"Please enter a user name. <br>";
    }

    if (isset($_POST["newPassword"])) {
        $pFlag = 1;
        echo "pFlag = 1<br>";
        $password = $_POST['newPassword'];
        unset($_POST['newPassword']);
    }else{
        echo "Please enter a password. <br>";
    }
    
    if (isset($_POST['fName'])) {
        $fnFlag = 1;
        echo "flFlag = 1<br>";
        $fName = $_POST['fName'];
        unset($_POST['fName']);
    }else{
        echo"Please enter a first name. <br>";
    }
    
    if (isset($_POST['lName'])) {
        $lnFlag = 1;
        echo "lnFlag = 1<br>";
        $lName = $_POST['lName'];
        unset($_POST['lName']);
    }else{
        echo"Please enter a last name. <br>";
    }

    if (isset($_POST['email'])) {
        $eFlag = 1;
        echo "eflag = 1<br>";
        $email = $_POST['email'];
        unset($_POST['email']);
    }else{
        echo"Please enter an email. <br>";
    }

    $flagTotal = $nFlag + $pFlag + $fnFlag + $lnFlag + $eFlag;
    echo "flagTotal = ". strval($flagTotal);

    //if all of the fields are set and unique then add a new record to the database
    //if ($flagTotal == 5){
        //$sql = "INSERT INTO users (userName, pass, realFirstName, realLastName, email) VALUES ('". $newUser. ", '". $password. "', '". $fName. "', '". $lName. "', '". $email. "');";
        //mysqli_query($pdo, $sql);
    //}

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