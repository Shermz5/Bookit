<?php
$passwords = [
    'adminpass1',
    'adminpass2',
    'password',
    'manager123',
    'rootpass'
];

foreach ($passwords as $password) {
    echo $password . " => " . password_hash($password, PASSWORD_DEFAULT) . "<br>";
}
?>
