<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;

$this->title = 'Главная';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Тестовое задание Python + PHP + OpenAI </h1>

        <p class="lead"></p>
    </div>

    <div class="body-content">

        <div class="row justify-content-center">
            <div class="col-md-2">
                <?= Html::a('Начать', ['/site/story'], ['class'=>'btn btn-primary']) ?>
            </div>
        </div>

    </div>
</div>
