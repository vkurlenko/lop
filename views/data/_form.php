<?php

//use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Data */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="data-form">

    <?php $form = ActiveForm::begin(); ?>

    <?/*= $form->field($model, 'code')->textInput() */?><!--

    --><?/*= $form->field($model, 'name')->textInput() */?>

    <?= $form->field($model, 'card')->textInput() ?>

    <?= $form->field($model, 'discount')->textInput() ?>

    <?= $form->field($model, 'user_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gender')->radioList(['женский', 'мужской']);//dropDownList([ '0' => 'женский', '1' => 'мужской', ], ['prompt' => 'Выберите пол']) ?>

    <?/*= $form->field($model, 'age')->textInput() */?><!--

    --><?/*= $form->field($model, 'born_year')->textInput() */?>

    <?= $form->field($model, 'born_date')->widget(\yii\jui\DatePicker::class, ['language' => 'ru', 'dateFormat' => 'yyyy-MM-dd']) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'subscribe')->checkbox([1, 0]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
