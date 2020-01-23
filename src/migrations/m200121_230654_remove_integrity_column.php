<?php

namespace snewer\images\migrations;

use yii\db\Migration;

/**
 * Class m200121_230654_remove_integrity_column
 */
class m200121_230654_remove_integrity_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%images}}', 'integrity');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200121_230654_remove_integrity_column cannot be reverted.\n";

        return false;
    }
}
