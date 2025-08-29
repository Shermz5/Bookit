<?php
// Run this script ONCE to generate and insert 5 admin users with real bcrypt hashes.
include 'db_connect.php';

$admins = [
    ['admin1', 'adminpass1'],
    ['admin2', 'adminpass2'],
    ['superuser', 'password'],
    ['manager', 'manager123'],
    ['root', 'rootpass'],
];

foreach ($admins as $admin) {
    $username = $admin[0];
    $plain = $admin[1];
    $hash = password_hash($plain, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admin (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hash);
    $stmt->execute();
    echo "Inserted $username with password $plain<br>";
    $stmt->close();
}
$conn->close();
echo "Done.";
?>
