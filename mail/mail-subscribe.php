<?php
use yii\helpers\Html;
?>

<h1><?=$subject?></h1>

<?=$body?>

<?=Html::img($message->embed($image))?>
