<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = 'Генерация сказки';


?>
<div class="site-index">
    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Генерация сказки</h1>

        <p class="lead"></p>
    </div>
    <div class="body-content">
        <div class="row">
            <div class="col-md-6">
                <?php
                $form = ActiveForm::begin();
                echo $form->field($model, 'age');
                echo $form->field($model, 'language')->dropDownList(['ru'=>'Русский','kk'=>'Казахский']);
                echo $form->field($model, 'characters')->checkboxList(['Заяц','Волк','Лиса','Алдар Көсе','Әйел Арстан']);
                echo Html::submitButton('Сгенерировать', ['class'=>'btn btn-primary']);
                ActiveForm::end();

                if ($storyText) {
                    echo "<hr>";
                    echo "<pre>".htmlspecialchars($storyText)."</pre>";
                }
                ?>
            </div>
        </div>
    </div>
</div>
