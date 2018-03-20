<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use apollo11\logger\DbTarget;
use yii\base\InvalidConfigException;
use yii\db\Migration;


/**
 * Initializes log table.
 *
 * The indexes declared are not required. They are mainly used to improve the performance
 * of some queries about message levels and categories. Depending on your actual needs, you may
 * want to create additional indexes (e.g. index on `log_time`).
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.1
 */
class m180320_111925_apollo11_log_table extends Migration
{
    /**
     * @var DbTarget[] Targets to create log table for
     */
    private $dbTargets = [];

    /**
     * @throws InvalidConfigException
     * @return DbTarget[]
     */
    protected function getDbTargets()
    {
        if ($this->dbTargets === []) {
            $log = Yii::$app->getLog();

            $usedTargets = [];
            foreach ($log->targets as $target) {
                if ($target instanceof DbTarget) {
                    $currentTarget = [
                        $target->db,
                        $target->logTable,
                    ];
                    if (!in_array($currentTarget, $usedTargets, true)) {
                        // do not create same table twice
                        $usedTargets[] = $currentTarget;
                        $this->dbTargets[] = $target;
                    }
                }
            }

            if ($this->dbTargets === []) {
                throw new InvalidConfigException('You should configure "log" component to use one or more database targets before executing this migration.');
            }
        }

        return $this->dbTargets;
    }

    public function up()
    {
        $targets = $this->getDbTargets();
        foreach ($targets as $target) {
            $this->db = $target->db;

            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            }

            $this->createTable($target->logTable, [
                'id' => $this->bigPrimaryKey(),
                'level' => $this->integer(),
                'category' => $this->string(),
                'log_time' => $this->double(),
                'prefix' => $this->text(),
                'message' => $this->text(),
                'text' => $this->text(),
            ], $tableOptions);

            $this->createIndex('idx_log_level', $target->logTable, 'level');
            $this->createIndex('idx_log_category', $target->logTable, 'category');
        }
    }

    public function down()
    {
        $targets = $this->getDbTargets();
        foreach ($targets as $target) {
            $this->db = $target->db;

            $this->dropTable($target->logTable);
        }
    }
}