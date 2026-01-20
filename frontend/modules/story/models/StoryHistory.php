<?php

namespace app\modules\story\models;

use yii\db\ActiveRecord;

class StoryHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%story_history}}';
    }

    public function rules(): array
    {
        return [
            [['age', 'language', 'characters', 'story_text', 'created_at'], 'required'],
            ['age', 'integer'],
            ['language', 'string', 'max' => 2],
            ['characters', 'string'],
            ['story_text', 'string'],
            ['created_at', 'safe'],
        ];
    }
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'age' => 'Возраст',
            'language' => 'Язык',
            'characters' => 'Персонажи',
            'story_text' => 'Текст сказки',
            'created_at' => 'Дата создания',
        ];
    }


}