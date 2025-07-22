<?php
// update_check_in_out.php

// —— DB CONFIG ——
$host = "localhost";
$username = "ncplicni_ncpldatabase";
$password = "Nirmaan@123";
$database = "ncplicni_ncpldatabase";

// connect
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// timezone
date_default_timezone_set('Asia/Kolkata');

// begin transaction
$conn->begin_transaction();

try {
    $today_date = date('Y-m-d');
    $today_start = $today_date . ' 00:00:00';
    $today_end = $today_date . ' 23:59:59';
    
    // 1) Update records for today to locked status
    // Fixed: Added proper quotes around date values in SQL query
    // Fixed: Used prepared statement to prevent SQL injection
    $sql = "UPDATE `tblforms` 
            SET `locked` = 1 
            WHERE `date` BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $today_start, $today_end);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction if everything succeeded
    $conn->commit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    // Log or handle the error appropriately
    error_log("Error in update_dpr_locked.php: " . $e->getMessage());
    // Optionally re-throw if you want calling code to handle it
    // throw $e;
}

$conn->close();