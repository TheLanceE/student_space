<?php
/**
 * Database Configuration Template
 * 
 * IMPORTANT: Copy this file to config.php and update the values for your environment
 * Never commit config.php to version control!
 */

class config {  
    private static $pdo = null;
   
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            // ============================================
            // UPDATE THESE VALUES FOR YOUR ENVIRONMENT
            // ============================================
            $servername = "localhost";      // Your database server
            $username = "root";             // Your database username
            $password = "";                 // Your database password
            $dbname = "edumind";            // Your database name
            $port = 3307;                   // Your MySQL port (default: 3307)
            // ============================================
            
            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;port=$port;dbname=$dbname", 
                    $username, 
                    $password
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                return self::$pdo;
                
            } catch (Exception $e) {
                die('<div style="color: red; padding: 20px; border: 1px solid red; margin: 10px;">
                     <h3>Database Connection Failed</h3>
                     <p>Error: ' . $e->getMessage() . '</p>
                     <p>Please make sure:</p>
                     <ul>
                         <li>XAMPP MySQL service is running</li>
                         <li>Database "edumind" exists</li>
                         <li>Check: <a href="http://localhost/phpmyadmin">phpMyAdmin</a></li>
                         <li>Update config.php with correct credentials</li>
                     </ul>
                     </div>');
            }
        }
        return self::$pdo;
    }
}
?>
