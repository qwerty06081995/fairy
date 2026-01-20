<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%story}}`.
 */
class m260120_100155_create_story_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%story_history}}', [
            'id' => $this->primaryKey(),
            'age' => $this->integer()->notNull(),
            'language' => $this->string(2)->notNull(),
            'characters' => $this->text()->notNull(),
            'story_text' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%story}}');
    }
}
