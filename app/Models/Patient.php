<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'blood_group',
        'address',
        'phone_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_history',
        'allergies',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns the patient profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointments for the patient.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the medical records for the patient.
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Get the prescriptions for the patient.
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the billings for the patient.
     */
    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    /**
     * Get the patient's age.
     *
     * @return int
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get the upcoming appointments for the patient.
     */
    public function upcomingAppointments()
    {
        return $this->appointments()
            ->where('appointment_date', '>=', now()->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');
    }

    /**
     * Get the past appointments for the patient.
     */
    public function pastAppointments()
    {
        return $this->appointments()
            ->where(function ($query) {
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
     * Get the recent medical records for the patient.
     */
    public function recentMedicalRecords()
    {
        return $this->medicalRecords()
            ->orderBy('record_date', 'desc')
            ->limit(5);
    }

    /**
     * Get the active prescriptions for the patient.
     */
    public function activePrescriptions()
    {
        return $this->prescriptions()
            ->where('status', 'active')
            ->orderBy('prescription_date', 'desc');
    }

    /**
     * Get the unpaid billings for the patient.
     */
    public function unpaidBillings()
    {
        return $this->billings()
            ->whereIn('payment_status', ['pending', 'partially_paid'])
            ->orderBy('bill_date');
    }
}
