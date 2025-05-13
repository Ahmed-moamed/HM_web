<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'department_id',
        'specialization',
        'qualification',
        'experience',
        'license_number',
        'consultation_fee',
        'bio',
        'profile_image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'experience' => 'integer',
    ];

    /**
     * Get the user that owns the doctor profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that the doctor belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the schedules for the doctor.
     */
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    /**
     * Get the appointments for the doctor.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the medical records created by the doctor.
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Get the prescriptions created by the doctor.
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the doctor's full name from the related user.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    /**
     * Get the doctor's email from the related user.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    /**
     * Get the doctor's profile image URL.
     *
     * @return string
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }

        return asset('images/default-doctor.png');
    }

    /**
     * Get the upcoming appointments for the doctor.
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
     * Get today's appointments for the doctor.
     */
    public function todaysAppointments()
    {
        return $this->appointments()
            ->where('appointment_date', now()->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_time');
    }

    /**
     * Get the past appointments for the doctor.
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
     * Get the active schedule for a specific day.
     *
     * @param string $day
     * @return \App\Models\DoctorSchedule|null
     */
    public function getScheduleForDay($day)
    {
        return $this->schedules()
            ->where('day_of_week', strtolower($day))
            ->where('status', 'active')
            ->first();
    }

    /**
     * Check if the doctor is available on a specific date and time.
     *
     * @param string $date
     * @param string $time
     * @return bool
     */
    public function isAvailable($date, $time)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $schedule = $this->getScheduleForDay($dayOfWeek);
        
        if (!$schedule) {
            return false;
        }
        
        // Check if the time is within the doctor's schedule
        if ($time < $schedule->start_time || $time > $schedule->end_time) {
            return false;
        }
        
        // Check if the doctor has reached the maximum number of appointments for that day
        $appointmentsCount = $this->appointments()
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        
        if ($appointmentsCount >= $schedule->max_appointments) {
            return false;
        }
        
        // Check if the doctor already has an appointment at that time
        $existingAppointment = $this->appointments()
            ->where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
        
        return !$existingAppointment;
    }

    /**
     * Get the available time slots for a specific date.
     *
     * @param string $date
     * @return array
     */
    public function getAvailableTimeSlots($date)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $schedule = $this->getScheduleForDay($dayOfWeek);
        
        if (!$schedule) {
            return [];
        }
        
        $startTime = strtotime($schedule->start_time);
        $endTime = strtotime($schedule->end_time);
        
        // Get appointment interval from settings (default to 30 minutes)
        $intervalMinutes = (int) Setting::where('setting_key', 'appointment_interval')->value('setting_value') ?? 30;
        $interval = $intervalMinutes * 60; // Convert to seconds
        
        $timeSlots = [];
        
        for ($time = $startTime; $time <= $endTime; $time += $interval) {
            $formattedTime = date('H:i:s', $time);
            
            // Check if this time slot is available
            if ($this->isAvailable($date, $formattedTime)) {
                $timeSlots[] = $formattedTime;
            }
        }
        
        return $timeSlots;
    }
}
