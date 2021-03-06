<?php

namespace apollo11\logger\models;

use Yii;
use apollo11\logger\DbTarget;

/**
 *
 * @property int $id
 * @property int $level
 * @property string $category
 * @property double $log_time
 * @property string $prefix
 * @property string $message
 * @property string $text
 * @property string $user_agent
 * @property string $remote_ip
 */
class SystemLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return self::getDbName();
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['log_time'], 'number'],
            [['prefix', 'message', 'text', 'user_agent'], 'string'],
            [['category', 'remote_ip'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level' => 'Level',
            'category' => 'Category',
            'log_time' => 'Log Time',
            'prefix' => 'Prefix',
            'message' => 'Message',
            'text' => 'Text',
            'user_agent' => 'User Agent',
            'remote_ip' => 'Remote Ip',
        ];
    }

    /**
     * @return mixed|string
     */
    public function getDbName(){
        $log = Yii::$app->getLog();
        foreach ($log->targets as $target) {
            if ($target instanceof DbTarget) {
                return $target->logTable;
            }
        }

        throw new LogRuntimeException('Unable to find db');
    }
}
