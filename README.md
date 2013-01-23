yii-sms
=======

Yii-расширение для работы с api сервиса [sms.ru](http://sms.ru)

## Установка

Загрузите yii-sms из этого репозитория github:

    cd protected/extensions
    git clone git://github.com/pimax/yii-sms.git

В protected/config/main.php внесите следующие строки:

    'components' => array
    (
        'sms' => array
        (
            'class'    => 'application.extensions.yii-sms.Sms',
            'login'     => 'username',      // Логин на сайте sms.ru
            'password'   => 'password',     // Пароль
        )
    );

## Использование

Отправка SMS:

    Yii::app()->sms->send('79251234567', 'Проверка отправки');
	Yii::app()->sms->send('79251234567', 'Проверка отправки', 'Имя отправителя', time(), $test = true, $partner_id);

Статус SMS:

    Yii::app()->sms->status('sms id');

Стоимость SMS:

    Yii::app()->sms->cost('79251234567', 'Проверка отправки');

Баланс:

    Yii::app()->sms->balance();

Дневной лимит:

    Yii::app()->sms->limit();

Отправители:

    Yii::app()->sms->senders();

Проверка валидности логина и пароля:

    Yii::app()->sms->check();