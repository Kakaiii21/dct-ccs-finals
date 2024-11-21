  
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
    $query = "SELECT * FROM users WHERE email = ? AND password = ? LIMIT 1";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Statement preparation failed: " . $conn->error);
    }

    $hashedPassword = md5($password);
    $stmt->bind_param("ss", $email, $hashedPassword); // Bind parameters (string, string)

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}


?>