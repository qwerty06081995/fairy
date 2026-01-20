<?php

/** @var yii\web\View $this */
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = 'Генератор сказок';
?>

<style>
    .btn-edoox{
        background-color: rgba(115, 89, 255, 1) !important;
    }
</style>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Сказки</h1>

        <p class="lead"></p>
    </div>

    <div class="body-content">
        <div class="row mb-2">
            <div class="col-md-2">
                <?= Html::a('Генерировать сказку', ['form'], ['class' => 'btn btn-success btn-edoox']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            'id',
                            'age',
                            'language',
                            [
                                'attribute' => 'characters',
                                'value' => function($model) {
                                    return $model->characters;
                                }
                            ],
                            'created_at:datetime',
                        ],
                        'summary' => false,
                    ]);
                ?>
            </div>
        </div>

    </div>
</div>
