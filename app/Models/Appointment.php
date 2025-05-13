<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'type',
        'reason',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i:s',
    ];

    /**
     * Get the patient that owns the appointment.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor that owns the appointment.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the medical record associated with the appointment.
     */
    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    /**
     * Get the billing associated with the appointment.
     */
    public function billing()
    {
        return $this->hasOne(Billing::class);
    }

    /**
     * Scope a query to only include upcoming appointments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now()->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }

    /**
     * Scope a query to only include past appointments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePast($query)
    {
        return $query->where(function ($query) {
                $query->where('appointment_date', '<', now()->format('Y-m-d'))
                    ->orWhere(function ($q) {
                        $q->where('appointment_date', '=', now()->format('Y-m-d'))
                            ->where('appointment_time', '<', now()->format('H:i:s'));
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc');
    }

    /**
     * Scope a query to only include today's appointments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->where('appointment_date', now()->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_time');
    }

    /**
     * Scope a query to only include pending appointments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed appointments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include completed appointments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include cancelled appointments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get the formatted appointment time.
     *
     * @return string
     */
    public function getFormattedTimeAttribute()
    {
        return date('h:i A', strtotime($this->appointment_time));
    }

    /**
     * Get the formatted appointment date.
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->appointment_date->format('F d, Y');
    }

    /**
     * Get the status badge HTML.
     *
     * @return string
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'completed' => 'info',
            'cancelled' => 'danger',
            'rescheduled' => 'primary',
        ];

        $color = $colors[$this->status] ?? 'secondary';

        return '<span class="badge bg-' . $color . '">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Check if the appointment can be cancelled.
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->appointment_date->greaterThanOrEqualTo(now()->addHours(24));
    }

    /**
     * Check if the appointment can be rescheduled.
     *
     * @return bool
     */
    public function canBeRescheduled()
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->appointment_date->greaterThanOrEqualTo(now()->addHours(24));
    }

    /**
     * Check if the appointment is upcoming.
     *
     * @return bool
     */
    public function isUpcoming()
    {
        return $this->appointment_date->greaterThanOrEqualTo(now()->startOfDay()) && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if the appointment is today.
     *
     * @return bool
     */
    public function isToday()
    {
        return $this->appointment_date->isToday() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if the appointment is past.
     *
     * @return bool
     */
    public function isPast()
    {
        return ($this->appointment_date->lessThan(now()->startOfDay()) || 
                ($this->appointment_date->isToday() && strtotime($this->appointment_time) < strtotime(now()->format('H:i:s')))) && 
               !in_array($this->status, ['cancelled']);
    }
}
