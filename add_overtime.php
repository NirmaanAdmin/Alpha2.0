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

// 0. (Optional) Ensure correct timezone
date_default_timezone_set('Asia/Kolkata');

// 1. Use today's date as the "work date"
$date_work = date('Y-m-d');

// 2. Fetch all timesheet entries on that date
$sql = "SELECT * FROM " . db_prefix() . "timesheets_timesheet WHERE date_work = '$date_work'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    error_log("No timesheet records for {$date_work}");
    $conn->close();
    exit;
}

while ($ts = $result->fetch_assoc()) {
    $staff_id = $ts['staff_id'];

    // 3. Load that staff's shift for that day
    $shift_sql = "SELECT st.time_start_work, st.time_end_work 
                 FROM " . db_prefix() . "work_shift_detail_number_day sd
                 LEFT JOIN " . db_prefix() . "shift_type st ON st.id = sd.shift_id
                 WHERE sd.staff_id = $staff_id";
    $shift_result = $conn->query($shift_sql);
    $shift = $shift_result->fetch_assoc();

    if (!$shift) {
        error_log("No shift for staff {$staff_id} on {$date_work}");
        continue;
    }

    // 4. First check-in
    $in_sql = "SELECT date FROM " . db_prefix() . "check_in_out 
              WHERE staff_id = $staff_id 
              AND type_check = 1 
              AND DATE(date) = '$date_work' 
              ORDER BY date ASC LIMIT 1";
    $in_result = $conn->query($in_sql);
    $in = $in_result->fetch_assoc();

    // 5. Last check-out
    $out_sql = "SELECT date FROM " . db_prefix() . "check_in_out 
               WHERE staff_id = $staff_id 
               AND type_check = 2 
               AND DATE(date) = '$date_work' 
               ORDER BY date DESC LIMIT 1";
    $out_result = $conn->query($out_sql);
    $out = $out_result->fetch_assoc();

    if (empty($in) || empty($out)) {
        error_log("Incomplete CICO for staff {$staff_id} on {$date_work}");
        continue;
    }

    // 6. Compute timestamps
    $actual_start = strtotime($in['date']);
    $actual_end   = strtotime($out['date']);

    // 7. Scheduled shift hours
    $sched_start = strtotime("{$date_work} {$shift['time_start_work']}");
    $sched_end   = strtotime("{$date_work} {$shift['time_end_work']}");
    $scheduled_h = max(0, ($sched_end - $sched_start) / 3600);

    // 8. Actual hours worked
    $actual_hours_logged = (float) $ts['value'];
    $actual_h = max(0, ($actual_end - $actual_start) / 3600);

    // 9. Overtime beyond scheduled
    $overtime = $actual_hours_logged - $scheduled_h;

    // 10. If ≥ 2.5 h, store the excess
    if ($overtime >= 2.5) {
        $time_in = date('Y-m-d H:i:s', $actual_start);
        $time_out = date('Y-m-d H:i:s', $actual_end);
        $overtime_value = $overtime - 2.5;
        
        $insert_sql = "INSERT INTO " . db_prefix() . "timesheets_additional_timesheet 
                      (staff_id, additional_day, time_in, time_out, timekeeping_value, reason, status, creator) 
                      VALUES 
                      ($staff_id, '$date_work', '$time_in', '$time_out', $overtime_value, 'Overtime', 1, $staff_id)";
        
        if ($conn->query($insert_sql) === TRUE) {
            // Success
        } else {
            // error_log("Error inserting overtime record: " . $conn->error);
        }
    }
}

// Close the database connection
$conn->close();
?>