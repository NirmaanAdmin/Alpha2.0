<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "root";
$database = "ncplivwx_ncpldatabase";

// Establish a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current date
$currentDate = date("Y-m-d");
$lastDayOfMonth = date("Y-m-t"); // Last day of the current month

// Only execute on the last day of the month
if ($currentDate === $lastDayOfMonth) {
    
    // Fetch all active employees from tblstaff
    $sqlFetchStaff = "SELECT staffid FROM tblstaff WHERE active = 1";
    $result = $conn->query($sqlFetchStaff);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $staffId = $row['staffid'];

            // Get the current year
           $currentYear = date("Y");
           
            // Add 1 day for CL (Casual Leave)
            $sqlCheckCL = "SELECT id, total, remain FROM tbltimesheets_day_off WHERE staffid = $staffId AND year = $currentYear AND type_of_leave = 'casual-leave-cl'";
           
            $resultCheckCL =  $conn->query($sqlCheckCL);
         
            
            if ($resultCheckCL->num_rows > 0) {

                // Update existing CL record
                $rowCL = $resultCheckCL->fetch_assoc();
                $id = $rowCL['id'];
                $remain = $rowCL['remain'] + 1.0;
                $newTotalCL = (float)$rowCL['total'] + 1.0;

                $sqlUpdateCL = "UPDATE tbltimesheets_day_off SET total = $newTotalCL, remain = $remain WHERE id = $id AND type_of_leave = 'casual-leave-cl'";
               
                if ($conn->query($sqlUpdateCL) === TRUE) {
                    echo "Record updated successfully CL";
                } else {
                    echo "Error updating record: " . $conn->error;
                }

            } 
            // Add 1 day for SL (Sick Leave)
            $sqlCheckSL = "SELECT id, total, remain FROM tbltimesheets_day_off WHERE staffid = $staffId AND year = $currentYear AND type_of_leave = 1";
           
            $resultCheckSL =  $conn->query($sqlCheckSL);
         
            
            if ($resultCheckSL->num_rows > 0) {

                // Update existing CL record
                $rowSL = $resultCheckSL->fetch_assoc();
                $id = $rowSL['id'];
                $remain = $rowSL['remain'] + 0.5;
                $newTotalSL = (float)$rowCL['total'] + 0.5;

                $sqlUpdateCL = "UPDATE tbltimesheets_day_off SET total = $newTotalSL, remain = $remain WHERE id = $id AND type_of_leave = 1";
               
                if ($conn->query($sqlUpdateCL) === TRUE) {
                    echo "Record updated successfully SL";
                } else {
                    echo "Error updating record: " . $conn->error;
                }

            } 
            
        }
    }
}

// Close the database connection
$conn->close();
?>
