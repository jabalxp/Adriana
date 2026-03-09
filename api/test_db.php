<?php
require_once 'require.php';
$result = mysqli_query($conn, "SHOW TABLES");
if (!$result) {
    die("Erro na consulta: " . mysqli_error($conn));
}

echo "Tabelas no banco de dados:\n";
while ($row = mysqli_fetch_row($result)) {
    echo "- " . $row[0] . "\n";
}
?>
