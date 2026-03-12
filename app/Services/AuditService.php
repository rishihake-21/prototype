<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(string $action, $auditable = null, array $oldValues = [], array $newValues = []): void
    {
        $data = [
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ];

        if ($auditable) {
            $data['auditable_type'] = get_class($auditable);
            $data['auditable_id'] = $auditable->id;
        }

        if (!empty($oldValues)) {
            $data['old_values'] = $this->filterSensitiveData($oldValues);
        }

        if (!empty($newValues)) {
            $data['new_values'] = $this->filterSensitiveData($newValues);
        }

        AuditLog::create($data);
    }

    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'remember_token', 'two_factor_secret'];
        
        return collect($data)->except($sensitiveFields)->toArray();
    }
}
