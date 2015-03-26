<?php

use yii\db\Schema;
use yii\db\Migration;

class m150325_143247_create_tree extends Migration
{
    public function up()
    {
        $this->createTable('tree', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING.' NOT NULL',
            'path' => Schema::TYPE_STRING.' NOT NULL DEFAULT \'.\'',
            'position' => Schema::TYPE_INTEGER.' NOT NULL DEFAULT 0',
            'level' => Schema::TYPE_INTEGER.' NOT NULL DEFAULT 0',
        ]);
    }

    public function down()
    {
        $this->dropTable('tree');
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
