<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display the appointment booking form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $departments = Department::all();
        $doctors = Doctor::with('user', 'department')->get();
        
        return view('appointments.create', compact('departments', 'doctors'));
    }

    /**
     * Store a newly created appointment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
            'type' => ['required', 'in:consultation,follow_up,emergency,routine_checkup'],
            'reason' => ['required', 'string'],
        ]);
        
        $validator->validate();
        
        $doctor = Doctor::findOrFail($request->doctor_id);
        
        // Check if the doctor is available at the selected time
        if (!$doctor->isAvailable($request->appointment_date, $request->appointment_time)) {
            return back()->withErrors(['appointment_time' => 'The selected time is not available.'])->withInput();
        }
        
        // Create the appointment
        $appointment = Appointment::create([
            'patient_id' => Auth::user()->patient->id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'pending',
            'type' => $request->type,
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);
        
        // Create notification for the doctor
        Notification::create([
            'user_id' => $doctor->user_id,
            'title' => 'New Appointment Request',
            'message' => 'You have a new appointment request from ' . Auth::user()->name . ' on ' . $appointment->formatted_date . ' at ' . $appointment->formatted_time,
            'type' => 'appointment',
            'data' => json_encode(['appointment_id' => $appointment->id]),
        ]);
        
        return redirect()->route('patient.appointments')->with('success', 'Appointment request submitted successfully. You will be notified once it is confirmed.');
    }

    /**
     * Display the specified appointment.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\View\View
     */
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\View\View
     */
    public function edit(Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        
        $departments = Department::all();
        $doctors = Doctor::with('user', 'department')->get();
        
        return view('appointments.edit', compact('appointment', 'departments', 'doctors'));
    }

    /**
     * Update the specified appointment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        
        $validator = Validator::make($request->all(), [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required'],
            'type' => ['required', 'in:consultation,follow_up,emergency,routine_checkup'],
            'reason' => ['required', 'string'],
        ]);
        
        $validator->validate();
        
        $doctor = Doctor::findOrFail($request->doctor_id);
        
        // Check if the doctor is available at the selected time (excluding this appointment)
        $isAvailable = true;
        if ($appointment->doctor_id != $request->doctor_id || 
            $appointment->appointment_date->format('Y-m-d') != $request->appointment_date || 
            $appointment->appointment_time->format('H:i:s') != $request->appointment_time) {
            
            $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->where('id', '!=', $appointment->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();
                
            $isAvailable = !$existingAppointment;
        }
        
        if (!$isAvailable) {
            return back()->withErrors(['appointment_time' => 'The selected time is not available.'])->withInput();
        }
        
        // Update the appointment
        $oldDoctorId = $appointment->doctor_id;
        
        $appointment->update([
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'type' => $request->type,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'status' => 'pending', // Reset to pending if changed
        ]);
        
        // Create notification for the doctor if changed
        if ($oldDoctorId != $request->doctor_id) {
            // Notify new doctor
            Notification::create([
                'user_id' => $doctor->user_id,
                'title' => 'New Appointment Request',
                'message' => 'You have a new appointment request from ' . Auth::user()->name . ' on ' . $appointment->formatted_date . ' at ' . $appointment->formatted_time,
                'type' => 'appointment',
                'data' => json_encode(['appointment_id' => $appointment->id]),
            ]);
            
            // Notify old doctor
            $oldDoctor = Doctor::findOrFail($oldDoctorId);
            Notification::create([
                'user_id' => $oldDoctor->user_id,
                'title' => 'Appointment Transferred',
                'message' => 'An appointment with ' . Auth::user()->name . ' has been transferred to another doctor.',
                'type' => 'appointment',
                'data' => json_encode(['appointment_id' => $appointment->id]),
            ]);
        } else {
            // Notify same doctor about changes
            Notification::create([
                'user_id' => $doctor->user_id,
                'title' => 'Appointment Updated',
                'message' => 'An appointment with ' . Auth::user()->name . ' has been updated to ' . $appointment->formatted_date . ' at ' . $appointment->formatted_time,
                'type' => 'appointment',
                'data' => json_encode(['appointment_id' => $appointment->id]),
            ]);
        }
        
        if (Auth::user()->isPatient()) {
            return redirect()->route('patient.appointments')->with('success', 'Appointment updated successfully.');
        } else {
            return redirect()->route('doctor.appointments')->with('success', 'Appointment updated successfully.');
        }
    }

    /**
     * Cancel the specified appointment.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);
        
        if (!$appointment->canBeCancelled()) {
            return back()->with('error', 'This appointment cannot be cancelled.');
        }
        
        $appointment->update([
            'status' => 'cancelled',
        ]);
        
        // Notify the other party
        $notifyUserId = Auth::user()->isPatient() ? $appointment->doctor->user_id : $appointment->patient->user_id;
        $notifierName = Auth::user()->name;
        
        Notification::create([
            'user_id' => $notifyUserId,
            'title' => 'Appointment Cancelled',
            'message' => 'An appointment on ' . $appointment->formatted_date . ' at ' . $appointment->formatted_time . ' has been cancelled by ' . $notifierName,
            'type' => 'appointment',
            'data' => json_encode(['appointment_id' => $appointment->id]),
        ]);
        
        if (Auth::user()->isPatient()) {
            return redirect()->route('patient.appointments')->with('success', 'Appointment cancelled successfully.');
        } else {
            return redirect()->route('doctor.appointments')->with('success', 'Appointment cancelled successfully.');
        }
    }

    /**
     * Confirm the specified appointment.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);
        
        $appointment->update([
            'status' => 'confirmed',
        ]);
        
        // Notify the patient
        Notification::create([
            'user_id' => $appointment->patient->user_id,
            'title' => 'Appointment Confirmed',
            'message' => 'Your appointment on ' . $appointment->formatted_date . ' at ' . $appointment->formatted_time . ' has been confirmed.',
            'type' => 'appointment',
            'data' => json_encode(['appointment_id' => $appointment->id]),
        ]);
        
        return redirect()->route('doctor.appointments')->with('success', 'Appointment confirmed successfully.');
    }

    /**
     * Mark the specified appointment as completed.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Appointment $appointment)
    {
        $this->authorize('complete', $appointment);
        
        $appointment->update([
            'status' => 'completed',
        ]);
        
        // Notify the patient
        Notification::create([
            'user_id' => $appointment->patient->user_id,
            'title' => 'Appointment Completed',
            'message' => 'Your appointment on ' . $appointment->formatted_date . ' at ' . $appointment->formatted_time . ' has been marked as completed.',
            'type' => 'appointment',
            'data' => json_encode(['appointment_id' => $appointment->id]),
        ]);
        
        return redirect()->route('doctor.appointments')->with('success', 'Appointment marked as completed successfully.');
    }

    /**
     * Get available time slots for a doctor on a specific date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimeSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);
        
        $doctor = Doctor::findOrFail($request->doctor_id);
        $timeSlots = $doctor->getAvailableTimeSlots($request->date);
        
        return response()->json([
            'timeSlots' => $timeSlots,
        ]);
    }
}
