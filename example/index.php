<?php

$userInfo = '';

if (isset($_GET['token'])) {
    $userInfo = file_get_contents('http://testauth.seymus.ru/user_info/?token=' . $_GET['token']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
<?= $userInfo; ?>
<a href="http://testauth.seymus.ru/vk/auth/?redirect=http://localhost:8000/">ВК авторизация</a>
</body>
</html>