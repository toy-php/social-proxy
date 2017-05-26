
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="assets/jquery-3.2.1.min.js"></script>
    <?php if (isset($_GET['token'])): ?>
        <script>
            $(document).ready(function () {
                $.get( "http://testauth.seymus.ru/user_info/", {token: '<?= $_GET['token'];?>'})
                    .done(function( data ) {
                        $('.response').html(JSON.stringify(data));
                    });
            });
        </script>

    <?php endif;?>
</head>
<body>
<div class="response"></div>
<p>
    <a href="http://testauth.seymus.ru/vk/auth/?redirect=http://localhost:8000/">ВК авторизация</a>
    <a href="http://testauth.seymus.ru/fb/auth/?redirect=http://localhost:8000/">FB авторизация</a>
</p>
</body>
</html>