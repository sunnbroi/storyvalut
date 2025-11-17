<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%message}}`.
 */
class m251113_131643_create_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%message}}', [
            'id'           => $this->primaryKey(),
            'author'       => $this->string(50)->notNull(),              
            'email'        => $this->string(255)->notNull(),            
            'message'      => $this->text()->notNull(),                  
            'ip'           => $this->string(45)->notNull(),             
            'created_at'   => $this->integer()->notNull(),
            'updated_at'   => $this->integer()->notNull(),
            'deleted_at'   => $this->integer()->null(),                  
            'edit_token'   => $this->char(64)->notNull()->unique(),      
            'delete_token' => $this->char(64)->notNull()->unique(),      
        ],);
        $this->tableOptions();
        $this->createIndex('idx-message-ip', '{{%message}}', 'ip');
        $this->createIndex('idx-message-created_at', '{{%message}}', 'created_at');
        $this->createIndex('idx-message-deleted_at', '{{%message}}', 'deleted_at');
        $this->createIndex('idx-message-ip-created_at', '{{%message}}', ['ip', 'created_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%message}}');
    }
    private function tableOptions(): ?string
    {
        if ($this->db->driverName === 'mysql') {
            return 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        }

        return null;
    }
}
