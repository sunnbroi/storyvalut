<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\behaviors\SoftDeleteBehavior;

/**
 * @property int         $id
 * @property string      $author
 * @property string      $email
 * @property string      $message
 * @property string      $ip
 * @property int         $created_at
 * @property int         $updated_at
 * @property int|null    $deleted_at
 * @property string      $edit_token
 * @property string      $delete_token
 */
class Message extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%message}}';
    }

    public function behaviors(): array
    {
        return [
            SoftDeleteBehavior::class,
        ];
    }

    /**
     * Маскированный IP вида 46.211.**.**
     */
    public function getMaskedIp(): string
    {
        if (strpos($this->ip, '.') !== false) {
            $parts = explode('.', $this->ip);
            if (count($parts) === 4) {
                $parts[2] = '**';
                $parts[3] = '**';
                return implode('.', $parts);
            }
        }
        return $this->ip;
    }

    /**
     * Относительное время создания: "10 минут назад".
     */
    public function getRelativeCreatedAt(): string
    {
        return Yii::$app->formatter->asRelativeTime($this->created_at);
    }

    public function getAuthorPostCountByIp(): int
    {
        return static::find()
            ->where(['ip' => $this->ip])
            ->andWhere(['deleted_at' => null])
            ->count();
    }

    public function softDelete(): bool
    {
        /** @var SoftDeleteBehavior $behavior */
        $behavior = $this->getBehavior(SoftDeleteBehavior::class);
        return $behavior ? $behavior->softDelete() : false;
    }
}