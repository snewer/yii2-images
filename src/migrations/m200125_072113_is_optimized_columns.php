<?php

namespace snewer\images\migrations;

use yii\db\Migration;

/**
 * Class m200125_072113_is_optimized_columns
 */
class m200125_072113_is_optimized_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%images}}', 'is_optimized', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%images}}', 'is_optimized');
    }
}
