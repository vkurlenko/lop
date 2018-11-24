<?php
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */

//debug($params);

?>
    <h2>Уважаемый(ая) <?=$user_name?>!</h2>

    <!--<h3>Ваш QR-код</h3>-->

    <p>Благодарим Вас за участие в нашей программе лояльности!</p>

    <p>Если Вы еще не являетесь пользователем приложения Pinbonus, то вам необходимо сделать три шага:</p>

    <ol>
        <li>Загрузить приложение из <?=Html::a('GooglePlay', 'https://play.google.com/store/apps/details?id=com.pinbonus2');?> или <?=Html::a('AppStore', 'https://itunes.apple.com/ru/app/diskontnye-i-bonusnye-karty/id904237311');?>;</li>
        <li>Найти Lion of Porches в приложении Pinbonus;</li>
        <li>Отсканировать Ваш уникальный QR-код в поле <em>Штрихкод</em>:
            <p><?=Html::img($message->embed($imageFileName), ['alt' => 'Ваш уникальный QR-код'])?></p>
            и сохранить карту.
            <br>
        </li>
    </ol>

<p><em>Предъявите электронную карту продавцу в магазине чтобы воспользоваться Вашей персональной скидкой.</em></p>

<!--https://play.google.com/store/apps/details?id=com.pinbonus2-->
<!--Если Вы пользователь IOS то Вам нужно просто перейти по этой ссылке.-->

    <!--<h3>Ссылка на электронную карту LOP</h3>

--><?/*=Html::a('http://lion-of-porches.ru/passes/sample.pkpass', 'http://lion-of-porches.ru/passes/sample.pkpass')*/?>
