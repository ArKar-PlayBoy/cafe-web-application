<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->logActivity('created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = [];
            
            foreach ($changes as $key => $value) {
                $original[$key] = $model->getOriginal($key);
            }
            
            $model->logActivity('updated', $original, $changes);
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted', $model->getAttributes(), null);
        });

        static::restored(function (Model $model) {
            $model->logActivity('restored', null, $model->getAttributes());
        });
    }

    protected function logActivity(string $action, ?array $oldValues, ?array $newValues): void
    {
        $isCritical = in_array($action, ['deleted']) || $this->isCriticalAction($action);
        
        AuditLog::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'action' => $this->getTable() . '.' . $action,
            'resource_type' => class_basename($this),
            'resource_id' => $this->getKey(),
            'old_values' => $this->filterSensitiveData($oldValues),
            'new_values' => $this->filterSensitiveData($newValues),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'is_critical' => $isCritical,
        ]);
    }

    protected function filterSensitiveData(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $sensitiveFields = ['password', 'remember_token', 'stripe_customer_id'];
        
        return array_diff_key($data, array_flip($sensitiveFields));
    }

    protected function isCriticalAction(string $action): bool
    {
        return in_array($action, ['deleted', 'force_deleted']);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'resource');
    }
}
