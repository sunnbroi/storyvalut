<?php

namespace app\models\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class SoftDeleteBehavior extends Behavior
{
    /**
     * Имя поля для soft-delete.
     */
    public string $deletedAtAttribute = 'deleted_at';

    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * Перехватываем обычный delete() и превращаем его в soft-delete.
     */
    public function beforeDelete($event): void
    {
        $owner = $this->owner;
        $attribute = $this->deletedAtAttribute;

        if ($owner instanceof ActiveRecord && $owner->hasAttribute($attribute)) {
            $owner->$attribute = time();
            // сохраняем только поле deleted_at без валидации
            $owner->save(false, [$attribute]);

            // отменяем физическое удаление
            $event->isValid = false;
        }
    }

    /**
     * Явный soft-delete, если не хочется вызывать delete().
     */
    public function softDelete(): bool
    {
        $owner = $this->owner;
        $attribute = $this->deletedAtAttribute;

        if ($owner instanceof ActiveRecord && $owner->hasAttribute($attribute)) {
            $owner->$attribute = time();
            return $owner->save(false, [$attribute]);
        }

        return false;
    }

    /**
     * Восстановление записи (может пригодиться позже).
     */
    public function restore(): bool
    {
        $owner = $this->owner;
        $attribute = $this->deletedAtAttribute;

        if ($owner instanceof ActiveRecord && $owner->hasAttribute($attribute)) {
            $owner->$attribute = null;
            return $owner->save(false, [$attribute]);
        }

        return false;
    }
}
