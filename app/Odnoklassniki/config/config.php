<?php

return [
    /*
        Идентификатор Вашего приложения.
     */
    'client_id' => '1251251968',

    /*
        Защищенный ключ Вашего приложения (указан в настройках приложения)
     */
    'client_secret' => '5BDCE43DD0ECCFF42741AB82',

    /*
        Внешний вид окна авторизации:
        * w – (по умолчанию) стандартное окно для полной версии сайта;
        * m – окно для мобильной авторизации;
        * a – упрощённое окно для мобильной авторизации без шапки.
     */
    'layout' => 'page',

    /*
        Битовая маска настроек доступа приложения,
        которые необходимо проверить при авторизации пользователя и запросить отсутствующие.
        смотреть: https://vk.com/dev/permissions
     */
    'scope' => 'email',

    /*
         Тип ответа, который Вы хотите получить.
     */
    'response_type' => 'code'
];