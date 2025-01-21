<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Chunks</title>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <h1>About</h1>
    <p>
        This project is being programmed by a Dad in his spare time (practically none). I am a retail
        manager with an Associates degree in Software Development. Most of the code is written in 
        PHP, but there is also considerable SQL, javascript, HTML, and CSS.
    </p>
    <p>
        The site is hosted on my personal webserver. Which is an extra laptop running apache 2 on
        Ubuntu Linux in my living room. My home internet provider doesnt allow a static IP, so I am
        using a tunneling service at <a href="www.ngrok.com">www.ngrok.com</a>.
        This allows my apache2 server to be seen from the web. The database for this website is a 
        mariaDB instance being run on the same computer.
    </p>

    <p>
        The site allows you to upload books or other documents in the form of .txt files. 
        It breaks the text up into sections of sentences as close to 50 words each as possible, and
        allows you to scroll through the book. You can choose different feeds that are loaded with 
        different books. <br>

        Future implementation will have the ability to filter in other
        things in between the individual book chunks. As well as add friends, and look at statistics.
    </p>

    <p>
        If you are curious, the code for this webpage is at
        <a href="https://github.com/SuirlCore/BookChunks"> My Github Page</a><br>
        You can also reach me with comments or suggestions by filling out the form
        On the Dev Notes page accessed in the menu.
    </p>

    <p>
        I also have a Code::Stats page at: <a href="https://codestats.net/users/Suirl">Suirl</a>
    </p>
    
    <img src="images/reliablyAptBuzzard.jpg" alt="reliably apt buzzard logo" style="width:300px;height:300px;">
</body>
</html>
