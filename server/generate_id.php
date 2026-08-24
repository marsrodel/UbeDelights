<?php
include 'db.php';

header('Content-Type: application/json');

$year = date('Y');
$generated_id = $year . '-0001';

try {
    $q = "SELECT MAX(CAST(SUBSTRING_INDEX(user_id, '-', -1) AS UNSIGNED)) AS maxseq FROM users WHERE LEFT(user_id, 4) = ?";
    $stmt = mysqli_prepare($connect, $q);
    mysqli_stmt_bind_param($stmt, "s", $year);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $maxseq = isset($row['maxseq']) ? (int)$row['maxseq'] : 0;
        mysqli_free_result($result);

        if ($maxseq >= 9999) {
            $year = (string)((int)$year + 1);
            $generated_id = $year . '-0001';
        } else {
            $nextSeq = $maxseq + 1;
            $suffix = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
            $generated_id = $year . '-' . $suffix;
        }
    }
    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    $generated_id = $year . '-0001';
}

echo json_encode(['id' => $generated_id]);

mysqli_close($connect);
?>
