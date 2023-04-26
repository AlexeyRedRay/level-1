<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body style = "margin: auto; width: 800px;">
    <?php
    $file = "session_counter.txt";
    $counter = file_get_contents($file);
    $counter++;
    file_put_contents($file, $counter);
    ?>
    <h1 style = "text-align: center;">- this is the page with a session counter -</h1>
    <a href="index.php">go to home page</a>
    <p>number of visits to this page: <?php echo  $counter?></p>
</body>
</html>