<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "root";
$database = "your_database_name";

// Establish a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 0. Ensure correct timezone
date_default_timezone_set('Asia/Kolkata');

$today = date('Y-m-d');

// 1. Only run on Mondays
if ((int)date('N', strtotime($today)) !== 1) {
    error_log("add_overtime skipped: not Monday ({$today})");
    $conn->close();
    exit;
}

// 2. Determine last Sunday's date and its weekday number (7)
$sunday = date('Y-m-d', strtotime('-1 day', strtotime($today)));
$dayNum = (int)date('N', strtotime($sunday)); // Sun => 7

// 3. Find all staff who clocked in/out on Sunday
$sql = "SELECT staff_id FROM " . db_prefix() . "check_in_out 
        WHERE DATE(date) = '$sunday' 
        GROUP BY staff_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    error_log("No one worked on Sunday ({$sunday})");
    $conn->close();
    exit;
}

while ($row = $result->fetch_assoc()) {
    $staff_id = $row['staff_id'];

    // 4. Skip if staff had a scheduled shift on Sunday (number = 7)
    $checkShiftSql = "SELECT COUNT(*) as count 
                     FROM " . db_prefix() . "work_shift_detail_number_day sd
                     WHERE sd.staff_id = $staff_id 
                     AND sd.number = $dayNum";
    $shiftResult = $conn->query($checkShiftSql);
    $shiftRow = $shiftResult->fetch_assoc();
    
    if ($shiftRow['count'] > 0) {
        // They had a regular Sunday shift; assume overtime is handled elsewhere
        continue;
    }

    // 5. First check-in on Sunday
    $inSql = "SELECT date FROM " . db_prefix() . "check_in_out 
              WHERE staff_id = $staff_id 
              AND type_check = 1 
              AND DATE(date) = '$sunday' 
              ORDER BY date ASC LIMIT 1";
    $inResult = $conn->query($inSql);
    $in = $inResult->fetch_assoc();

    // 6. Last check-out on Sunday
    $outSql = "SELECT date FROM " . db_prefix() . "check_in_out 
               WHERE staff_id = $staff_id 
               AND type_check = 2 
               AND DATE(date) = '$sunday' 
               ORDER BY date DESC LIMIT 1";
    $outResult = $conn->query($outSql);
    $out = $outResult->fetch_assoc();

    // 7. Compute worked hours
    $startTs = strtotime($in['date']);
    $endTs = strtotime($out['date']);
    $hoursWorked = max(0, ($endTs - $startTs) / 3600);

    // 8. Insert as full-day Sunday overtime
    $time_in = date('Y-m-d H:i:s', $startTs);
    $time_out = date('Y-m-d H:i:s', $endTs);
    
    $insertSql = "INSERT INTO " . db_prefix() . "timesheets_additional_timesheet 
                 (staff_id, additional_day, time_in, time_out, timekeeping_value, reason, status, creator) 
                 VALUES 
                 ($staff_id, '$sunday', '$time_in', '$time_out', $hoursWorked, 'Sunday work', 1, $staff_id)";
    
    if ($conn->query($insertSql)) {
        // Success
        // error_log("Recorded {$hoursWorked}h Sunday-overtime for staff {$staff_id}");
    } else {
        // error_log("Error recording Sunday overtime for staff {$staff_id}: " . $conn->error);
    }
}

// Close the database connection
$conn->close();
?>