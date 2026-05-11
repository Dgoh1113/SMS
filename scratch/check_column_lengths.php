<?php
$host = '192.168.101.227';
$db = 'C:\Users\User\source\SMS.FDB';
$user = 'SYSDBA';
$pass = 'masterkey';

try {
    $dbh = new PDO("firebird:dbname=$host:$db", $user, $pass);
    $query = "SELECT 
                R.RDB\$FIELD_NAME AS FIELD_NAME,
                F.RDB\$FIELD_LENGTH AS FIELD_LENGTH,
                F.RDB\$FIELD_TYPE AS FIELD_TYPE
              FROM RDB\$RELATION_FIELDS R
              JOIN RDB\$FIELDS F ON R.RDB\$FIELD_SOURCE = F.RDB\$FIELD_NAME
              WHERE R.RDB\$RELATION_NAME = 'LEAD'
              ORDER BY R.RDB\$FIELD_POSITION";
              
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Column lengths for table LEAD:\n";
    foreach ($columns as $col) {
        echo trim($col['FIELD_NAME']) . ": " . $col['FIELD_LENGTH'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
