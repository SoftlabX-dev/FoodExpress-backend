<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'priority',
        'read_at',
        'resolved_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Déterminer la priorité automatiquement selon le sujet
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            $report->priority = self::determinePriority($report->subject);
        });
    }

    public static function determinePriority($subject)
    {
        if (stripos($subject, 'Order Issue') !== false) {
            return 'high';
        } elseif (stripos($subject, 'Delivery Delay') !== false) {
            return 'medium';
        }
        return 'low';
    }

    // Scopes pour filtrage
  public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }}
