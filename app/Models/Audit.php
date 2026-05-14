<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as OwenAudit;

class Audit extends OwenAudit
{
    protected static function booted(): void
    {
        static::creating(function (Audit $audit) {
            $audit->checksum = $audit->makeChecksum();
        });
    }

    public function verifyChecksum(): bool
    {
        return $this->checksum === $this->makeChecksum();
    }

    public function makeChecksum(): string
    {
        return hash('sha256', json_encode([
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'event' => $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'url' => $this->url,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'method' => $this->method,
            'tags' => $this->tags,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
