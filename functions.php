  
<?php    
function databaseConnection(): mysqli {
   $server = 'localhost';
   $username = 'root';       
   $password = "";            
   $dbname = 'dct-ccs-finals'; 

   
   $conn = new mysqli($server, $username, $password, $dbname);

   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }

   return $conn;
}
function userLogin($email, $password) {
    $conn = databaseConnection();
    $query = "SELECT * FROM users WHERE email = :email AND password = :password LIMIT 1";
    $stmt = $conn->prepare($query);
    $hashedPassword = md5($password);
    $stmt->execute(['email' => $email, 'password' => $hashedPassword]);

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

?>