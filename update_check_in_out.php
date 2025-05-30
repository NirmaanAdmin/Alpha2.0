<?php
// update_check_in_out.php

// —— DB CONFIG ——
$host     = "localhost";
$username = "root";
$password = "root";
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
    $today = date("Y-m-d");

    // 1) aggregate per staff: count, min & max datetime
    $sql = "
        SELECT 
          staff_id,
          COUNT(*)          AS cnt,
          MIN(date) AS time_in,
          MAX(date) AS time_out
        FROM tblcheck_in_out
        WHERE DATE(date) = '{$today}'
        GROUP BY staff_id
    ";
    $res = $conn->query($sql);

    $processed = 0;
    while ($row = $res->fetch_assoc()) {
        $staffId = (int)$row['staff_id'];
        $cnt     = (int)$row['cnt'];

        // skip single-entry days
        if ($cnt < 2) {
            continue;
        }

        // extract time_in / time_out
        $time_in  = $row['time_in'];
        $time_out = $row['time_out'];

        // —— your snippet: compute total hours ——
        // ensure ordering
        $d1 = strtotime($time_in);
        $d2 = strtotime($time_out);
        if ($d1 > $d2) {
            list($time_in, $time_out, $d1, $d2) = 
                [$time_out, $time_in, $d2, $d1];
        }
        $total_hours = round(abs($d2 - $d1) / 3600, 2);

        // prepare upsert data
        $value = $total_hours;
        $type  = 'W';    // or change to 'H' if you prefer
        $date_work = $today;

        // check existing
        $chk = $conn->query("
            SELECT id 
            FROM tbltimesheets_timesheet 
            WHERE staff_id = {$staffId} 
              AND date_work = '{$date_work}' 
            LIMIT 1
        ");

        if ($chk->num_rows) {
            // UPDATE
            $id = $chk->fetch_assoc()['id'];
            $upd = "
                UPDATE tbltimesheets_timesheet
                SET value     = {$value},
                    type      = '{$type}',
                    date_work = '{$date_work}',
                    staff_id  = {$staffId}
                WHERE id = {$id}
            ";
            $conn->query($upd);
        } else {
            // INSERT
            $ins = "
                INSERT INTO tbltimesheets_timesheet
                  (staff_id, date_work, value, type, add_from)
                VALUES
                  ({$staffId}, '{$date_work}', {$value}, '{$type}', 1)
            ";
            $conn->query($ins);
        }

        $processed++;
    }

    // commit
    $conn->commit();
    echo "[".date('Y-m-d H:i:s')."] Synced {$processed} staff(s) with >1 entries.\n";

} catch (Exception $e) {
    $conn->rollback();
    echo "Cron job failed: " . $e->getMessage() . "\n";
}

$conn->close();
