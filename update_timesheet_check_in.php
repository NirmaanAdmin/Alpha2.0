<?php
// DB config
$dbHost = 'localhost';
$dbUser = 'ncplicni_ncpldatabase';
$dbPass = 'Nirmaan@123';
$dbName = 'ncplicni_ncpldatabase';

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $conn = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Insert only when a timesheet for that staff_id + date does not exist.
    // value = shift duration in HOURS (decimal), computed even if the shift crosses midnight.
    // Shift selection: latest row per staff_id from tblwork_shift_detail_number_day -> shift_id -> tblshift_type.
    $sql = "
  INSERT INTO tbltimesheets_timesheet (staff_id, date_work, value, type, add_from)
  SELECT
      t.staff_id,
      t.date_work,
      COALESCE(
        ROUND(((TIME_TO_SEC(st.time_end_work)-TIME_TO_SEC(st.time_start_work)+86400)%86400)/3600, 2),
        0
      ) AS value_hours,
      'W', 1
  FROM (
      SELECT cio.staff_id, DATE(cio.date) AS date_work
      FROM tblcheck_in_out AS cio
      WHERE cio.type_check = 1
        AND DATE(cio.date) <> CURDATE()   -- ← skip today's date
      GROUP BY cio.staff_id, DATE(cio.date)
      HAVING COUNT(*) = 1
  ) AS t
  LEFT JOIN (
      SELECT wsd.staff_id, wsd.shift_id
      FROM tblwork_shift_detail_number_day AS wsd
      JOIN (
          SELECT staff_id, MAX(id) AS max_id
          FROM tblwork_shift_detail_number_day
          GROUP BY staff_id
      ) AS latest
        ON latest.staff_id = wsd.staff_id AND latest.max_id = wsd.id
  ) AS s  ON s.staff_id = t.staff_id
  LEFT JOIN tblshift_type AS st
         ON st.id = s.shift_id
  LEFT JOIN tbltimesheets_timesheet AS tst
         ON tst.staff_id = t.staff_id
        AND tst.date_work = t.date_work
  WHERE tst.staff_id IS NULL
";


    $stmt = $conn->prepare($sql);
    $stmt->execute();

    echo "Success: Inserted " . $stmt->rowCount() . " record(s) into tbltimesheets_timesheet";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn = null;
}
