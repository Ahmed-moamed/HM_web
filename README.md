# Hospital Management System

A comprehensive Hospital Management System built with Laravel and MySQL for the backend, and HTML, CSS, and JavaScript for the frontend.

## Features

- **User Authentication**
  - Registration (Doctor/Patient)
  - Login/Logout
  - Password reset

- **User Profiles**
  - Patient profile with medical history
  - Doctor profile with specialization, availability

- **Appointment System**
  - Book appointments
  - View/Cancel appointments
  - Doctor availability calendar

- **Chat System**
  - Real-time messaging between doctors and patients
  - Message notifications

- **Notification System**
  - Appointment reminders
  - New message alerts
  - Medical updates

- **Admin Dashboard**
  - Manage users
  - View statistics
  - Handle appointments

- **Medical Records**
  - Patient medical history
  - Diagnosis and treatment records
  - Prescription management

- **Billing System**
  - Generate invoices
  - Payment tracking
  - Service pricing

## Technology Stack

- **Backend**
  - PHP 8.1+
  - Laravel 10.x
  - MySQL 8.0+

- **Frontend**
  - HTML5
  - CSS3
  - JavaScript
  - Bootstrap 5

- **Additional Libraries**
  - Font Awesome (Icons)
  - Chart.js (Statistics)

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL
- Node.js and NPM

### Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/hospital-management-system.git
   cd hospital-management-system
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure the database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hospital_management
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Run database migrations and seed the database:
   ```bash
   php artisan migrate --seed
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

8. Visit `http://localhost:8000` in your browser.

## Default Users

After seeding the database, you can use the following default users to log in:

- **Admin**
  - Email: admin@medicare.com
  - Password: password

- **Doctor**
  - Email: doctor@medicare.com
  - Password: password

- **Patient**
  - Email: patient@medicare.com
  - Password: password

## Project Structure

- `app/` - Contains the core code of the application
  - `Http/Controllers/` - Controllers for handling HTTP requests
  - `Models/` - Eloquent models representing database tables
  - `Policies/` - Authorization policies
  - `Providers/` - Service providers

- `database/` - Database migrations and seeders
  - `migrations/` - Database table structures
  - `seeders/` - Default data for the database
  - `schema.sql` - Complete database schema

- `public/` - Publicly accessible files
  - `css/` - Stylesheets
  - `js/` - JavaScript files
  - `images/` - Image assets

- `resources/` - Frontend resources
  - `views/` - Blade templates
  - `css/` - SCSS source files
  - `js/` - JavaScript source files

- `routes/` - Application routes
  - `web.php` - Web routes
  - `api.php` - API routes

## Database Schema

The database consists of the following main tables:

- `users` - User authentication information
- `patients` - Patient-specific information
- `doctors` - Doctor-specific information
- `departments` - Hospital departments
- `appointments` - Appointment records
- `medical_records` - Patient medical records
- `prescriptions` - Medication prescriptions
- `messages` - Chat messages
- `notifications` - User notifications
- `services` - Hospital services
- `billings` - Patient billing information

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Commit your changes: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature-name`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Laravel](https://laravel.com/)
- [Bootstrap](https://getbootstrap.com/)
- [Font Awesome](https://fontawesome.com/)
- [Chart.js](https://www.chartjs.org/)
