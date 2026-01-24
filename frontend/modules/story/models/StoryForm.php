<?php

namespace app\modules\story\models;

use yii\base\Model;

class StoryForm extends Model
{
    public $age;
    public $language;
    public $genre;
    public $characters = [];

    public function rules(): array
    {
        return [
            [['age', 'language', 'characters'], 'required'],
            ['age', 'integer', 'min' => 1],
            ['language', 'in', 'range' => ['ru', 'kk']],
            ['genre', 'required'],

            ['characters', 'each', 'rule' => ['string']],
        ];
    }
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'age' => 'Возраст',
            'language' => 'Язык',
            'genre' => 'Жанр',
            'characters' => 'Персонажи',
            'story_text' => 'Текст сказки',
            'created_at' => 'Дата создания',
        ];
    }

}