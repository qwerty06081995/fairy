<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = 'Генерация сказки';


?>
<style>
    .markdown-body{
        overflow-y: scroll;
        height: 300px;
    }
</style>
<div class="site-index">
    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Генерация сказки</h1>

        <p class="lead"></p>
    </div>
    <div class="body-content">
        <div class="row">
            <div class="col-md-6">
                <?php
                $form = ActiveForm::begin([
                    'id' => 'story-form',
                    'enableClientValidation' => false,
                    'enableAjaxValidation'   => false,
                ]);
                echo $form->field($model, 'age');
                echo $form->field($model, 'language')->dropDownList(['ru'=>'Русский','kk'=>'Казахский']);
                echo $form->field($model, 'characters')->checkboxList(['Заяц','Волк','Лиса','Алдар Көсе','Әйел Арстан']);
                echo $form->field($model, 'genre')->dropDownList([
                    'Приключения',
                    'Фэнтези',
                    'Волшебная сказка',
                    'Комедия',
                    'Драма',
                    'Сказка о животных',
                    'Семейная сказка',
                    'Поучительная сказка',
                    'Детектив',
                    'Путешествие'
                ]);
                echo Html::button('Сгенерировать сказку', [
                    'class' => 'btn btn-primary',
                    'id' => 'generate-btn',
                    'type' => 'button', // ❗ ВАЖНО
                ]);
                ActiveForm::end();

//                if ($storyText) {
//                    echo "<hr>";
//                    echo "<pre>".htmlspecialchars($storyText)."</pre>";
//                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div id="story-output" class="markdown-body"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('generate-btn').addEventListener('click', async () => {

        const form   = document.getElementById('story-form');
        const output = document.getElementById('story-output');

        output.innerHTML = '';

        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');

        let markdown = '';

        while (true) {
            const { value, done } = await reader.read();
            if (done) {

                break;
            }

            markdown += decoder.decode(value, { stream: true });
            output.innerHTML = marked.parse(markdown);
        }
    });
</script>


