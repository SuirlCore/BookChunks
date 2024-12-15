<?php 
    session_start();
    include 'bleachcontaminate.php';
	include 'pdo.php';

    $nFlag = 0;
    $pFlag = 0;
    $fnFlag = 0;
    $lnFlag = 0;
    $eFlag = 0;

    if (isset($_POST['newUser'])) {
        $userName = $_POST['newUser'];
        
        $sql = "CALL checkUserName2('". $userName. "', @result);";

        //echo $sql. "<br>";

        $run = mysqli_query($pdo, $sql);

        if (mysqli_num_rows($run) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($run)) {
              //echo "resultName = ". $row["resultName"]. "<br>";
              $requests = $row["resultName"];
            }
          }

        if ($requests == "False"){
            $nFlag = 1;
            //echo "nFlag = 1<br>";
        }else{
            echo "This username is already taken. Please choose another. <br>";
        }

        $newUser = $_POST['newUser'];
        unset($_POST['newUser']);
    }else{
        echo "Please enter a user name. <br>";
    }

    if (isset($_POST["newPassword"])) {
        $pFlag = 1;
        //echo "pFlag = 1<br>";
        $password = $_POST['newPassword'];
        unset($_POST['newPassword']);
    }else{
        echo "Please enter a password. <br>";
    }
    
    if (isset($_POST['fName'])) {
        $fnFlag = 1;
        //echo "flFlag = 1<br>";
        $fName = $_POST['fName'];
        unset($_POST['fName']);
    }else{
        echo"Please enter a first name. <br>";
    }
    
    if (isset($_POST['lName'])) {
        $lnFlag = 1;
        //echo "lnFlag = 1<br>";
        $lName = $_POST['lName'];
        unset($_POST['lName']);
    }else{
        echo"Please enter a last name. <br>";
    }

    if (isset($_POST['email'])) {
        $eFlag = 1;
        //echo "eflag = 1<br>";
        $email = $_POST['email'];
        unset($_POST['email']);
    }else{
        echo"Please enter an email. <br>";
    }

    $flagTotal = $nFlag + $pFlag + $fnFlag + $lnFlag + $eFlag;
    //echo "flagTotal = ". strval($flagTotal). "<br>";

    //if all of the fields are set and unique then add a new record to the database
    if ($flagTotal == 5){
        $sql = "INSERT INTO users (userName, pass, realFirstName, realLastName, email) VALUES ('". $newUser. "', '". $password. "', '". $fName. "', '". $lName. "', '". $email. "');";
        //echo $sql. "<br>";
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