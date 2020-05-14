<?php
require_once 'db_info.php';
session_start();

if(!isset($_SESSION['id']) && empty($_SESSION['id'])) {
  header('Location: login.php');     
}

function htmlHeader()
{
  return
    <<<_END
            <!DOCTYPE html>
            <html lang="en">
            
            <head>
              <meta charset="UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <link rel="stylesheet" href="style.css">
              <title>Add Posts</title>
            </head>
            
            <body>
              <div id="main-container">
                <div id="main-container">
                  <div class="top-menu">
                  <span class="menu-items"><a href="homepage.php">Home</a></span>                    
                  <span class="menu-items"><a href="logout.php">Logout</a></span>              
                </div>
                <h1>Add a new post</h1>
                <div id="container">
                  <div id="container-post">
                    <form method="post" action="addpost.php" enctype="multipart/form-data">
                      <p>Title</p>
                      <input type="text" name="title">
                      <p>Description</p>
                      <textarea name="description"></textarea>
                      <p>Image</p>
                      <input type="file" name="file">
                      <div id="buttons">
                        <button name="upload">Add a post</button>
                        <button>Cancel</button>
                      </div>
                    </form>
      
        _END;
}


function htmlForm(){
  
}


function main()
{
  $formDoc = htmlHeader();
  echo $formDoc;
  $id = $_SESSION['id'];
  echo "ID: " . $id;
  $htmlContent = "";
  if (isset($_POST['upload'])) {
    global $hn, $un, $pw, $db;
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die("Connection failed!");

    $target_dir = "upload/";
    $target_file = $target_dir . basename($_FILES["file"]["name"]);

    // Select file type
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Valid file extensions
    $extensions_arr = array("jpg", "jpeg", "png", "gif");

    // Check extension
    if (in_array($imageFileType, $extensions_arr)) {
      // Convert to base64
      $image_base64 = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
      $image = 'data:image/' . $imageFileType . ';base64,' . $image_base64;

      // echo "base64 code is: " . $image;

      $Image = sanitizeMySQL($conn, $image);
      $image_title = sanitizeMySQL($conn, sanitizeString($_POST['title']));
      $image_text = sanitizeMySQL($conn, sanitizeString($_POST['description']));
      $ID = sanitizeMySQL($conn, $id);

      $stmt = $conn->prepare("INSERT INTO Post(title,text,image,author_id) VALUES (?,?,?,?)");
      $stmt->bind_param('ssss', $image_title, $image_text, $Image, $ID);
      $stmt->execute();
    } else {
      $htmlContent .= "<span class='error' style='text-align:center'>Sorry.Wrong image type!!!</span>";
    }
  }
  $htmlContent .= "
          </div>
        </div>
      </div>
    </body>            
  </html>                        
  ";
  echo $htmlContent;
}

function sanitizeString($var)
{
  $var = stripslashes($var);
  $var = strip_tags($var);
  $var = htmlentities($var);
  return $var;
}

function sanitizeMySQL($connection, $var)
{
  $var = $connection->real_escape_string($var);
  $var = sanitizeString($var);
  return $var;
}

main();
