<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "root";
$database = "ncplicni_ncpldatabase";

// Establish a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start transaction for atomic operations
$conn->begin_transaction();

try {
    // Get current year
    $currentYear = date("Y");

    // Query to find timesheets with timekeeping_value >= 4
    $sqlTimesheets = "SELECT id, staff_id, timekeeping_value, com_off 
                      FROM tbltimesheets_additional_timesheet 
                      WHERE timekeeping_value >= 4 
                      AND (com_off IS NULL OR com_off = 0)"; // Only process unprocessed records

    $resultTimesheets = $conn->query($sqlTimesheets);

    if ($resultTimesheets->num_rows > 0) {
        while ($row = $resultTimesheets->fetch_assoc()) {
            $timesheetId = $row['id'];
            $staffId = $row['staff_id'];
            $timekeepingValue = $row['timekeeping_value'];

            // Determine days to add based on timekeeping_value
            if ($timekeepingValue >= 9.5) {
                $daysToAdd = 1.0;
                $comOffValue = 1; // Mark as processed with 1 day
            } else {
                $daysToAdd = 0.5;
                $comOffValue = 0.5; // Mark as processed with 0.5 day
            }

            // Check for existing compensatory leave record
            $sqlCheckCompOff = "SELECT id, total, remain FROM tbltimesheets_day_off 
                               WHERE staffid = $staffId 
                               AND year = $currentYear 
                               AND type_of_leave = 'compensatory-leave-comp-off'";

            $resultCheckCompOff = $conn->query($sqlCheckCompOff);

            if ($resultCheckCompOff->num_rows > 0) {
                // Update existing comp-off record
                $rowCompOff = $resultCheckCompOff->fetch_assoc();
                $id = $rowCompOff['id'];
                $remain = $rowCompOff['remain'] + $daysToAdd;
                $newTotal = (float)$rowCompOff['total'] + $daysToAdd;

                $sqlUpdateCompOff = "UPDATE tbltimesheets_day_off 
                                    SET total = $newTotal, remain = $remain 
                                    WHERE id = $id 
                                    AND type_of_leave = 'compensatory-leave-comp-off'";

                if (!$conn->query($sqlUpdateCompOff)) {
                    throw new Exception("Error updating comp-off record: " . $conn->error);
                }
            } else {
                // Insert new comp-off record if none exists
                $sqlInsertCompOff = "INSERT INTO tbltimesheets_day_off 
                                    (staffid, year, type_of_leave, total, remain) 
                                    VALUES 
                                    ($staffId, $currentYear, 'compensatory-leave-comp-off', $daysToAdd, $daysToAdd)";

                if (!$conn->query($sqlInsertCompOff)) {
                    throw new Exception("Error creating comp-off record: " . $conn->error);
                }
            }

            // Update the timesheets_additional_timesheet to mark as processed
            $sqlUpdateTimesheet = "UPDATE tbltimesheets_additional_timesheet 
                                  SET com_off = $comOffValue 
                                  WHERE id = $timesheetId";

            if (!$conn->query($sqlUpdateTimesheet)) {
                throw new Exception("Error updating timesheet record: " . $conn->error);
            }

            // Log successful processing
            // error_log("Processed timesheet ID $timesheetId for staff $staffId - Added $daysToAdd comp-off days");
        }
    } else {
        // error_log("No timesheets found with timekeeping_value >= 4 or all have been processed");
    }

    // Commit transaction if all queries succeeded
    $conn->commit();
    echo "Cron job completed successfully at " . date('Y-m-d H:i:s');
} catch (Exception $e) {
    // Roll back transaction on error
    $conn->rollback();
    // error_log("Cron job failed: " . $e->getMessage());
    echo "Cron job failed: " . $e->getMessage();
}

// Close connection
$conn->close();
