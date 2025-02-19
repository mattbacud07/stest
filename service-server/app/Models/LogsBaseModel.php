<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogsBaseModel extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            static::logChanges($model, 'created', $model->toArray(), null);
        });

        static::updated(function ($model) {
            static::logChanges($model, 'updated', $model->getChanges(), $model->getOriginal());
        });

        static::deleted(function ($model) {
            static::logChanges($model, 'deleted', null, $model->getOriginal());
        });
    }

    protected static function logChanges($model, $action, $changes = null, $original = null)
    {
        Logs::create([
            'model' => class_basename($model),
            'model_name' => defined(get_class($model) . '::model_name') 
            ? constant(get_class($model) . '::model_name') 
            : class_basename($model),
            'model_id' => $model->id,
            'action' => $action,
            'changes' => $changes ? json_encode($changes) : null,
            'original' => $original ? json_encode($original) : null,
            'user_id' => Auth::id(),
        ]);
    }
}
