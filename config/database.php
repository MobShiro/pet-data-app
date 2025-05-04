<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "vet_anywhere";
    private $conn;
    
    // Constructor - establishes database connection when class is instantiated
    public function __construct() {
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            // Check connection
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set charset to ensure proper encoding
            $this->conn->set_charset("utf8");
            
        } catch (Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection error. Please try again later.");
        }
    }
    
    // Get the connection object
    public function getConnection() {
        return $this->conn;
    }
    
    // Prepare a statement with error handling
    public function prepare($sql) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare statement error: " . $this->conn->error . " for SQL: " . $sql);
            throw new Exception("Database error. Please try again later.");
        }
        return $stmt;
    }
    
    // Execute a simple query
    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            error_log("Query error: " . $this->conn->error . " for SQL: " . $sql);
            throw new Exception("Database query error. Please try again later.");
        }
        return $result;
    }
    
    // Close database connection
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    // Escape strings to prevent SQL injection
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
}
?>  