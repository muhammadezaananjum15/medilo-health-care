# Medilo Medical Appointment & Healthcare Management System
## Comprehensive System & Architecture Documentation

### 1. Executive Summary
Medilo is an end-to-end medical management and doctor appointment platform built with clean PHP, PDO prepared statements, and a modern medical responsive user interface. It connects patients with verified specialist practitioners across multiple cities, providing real-time availability scheduling, instant appointment reservations, electronic health records, and simulated payment gateway processing (Stripe Card & PayPal).

---

### 2. Core Functional Modules

#### 2.1 Public Frontend & Portal
- **Interactive Hero Carousel**: Smooth, multi-slide hero section featuring dynamic backgrounds, key value propositions, call-to-actions, and synchronous thumbnail navigation.
- **Doctor Search & Filtering**: Multi-criteria search allowing patients to find physicians by full name, medical specialty, and practice city.
- **Doctor Detailed Profiles**: Comprehensive profiles showcasing qualifications, years of experience, practice cities, consultation fees, bio, and live weekly availability schedules.
- **Services Catalog**: Dedicated catalog of medical departments and specialized services (Cardiology, Neurology, Pediatrics, Orthopedics, Dermatology, Dentistry, Ophthalmology, etc.).
- **Diseases, Preventions & Cures**: Dynamic medical knowledge directory categorized into preventable conditions, clinical diseases, and therapeutic cures.
- **Medical News & Health Articles**: Up-to-date health news feed with detailed article reading views.
- **Clinic Contact & Location**: Direct contact form, clinic headquarters address, 24/7 hotline numbers, and operating hours.

#### 2.2 Patient Management & Booking
- **Step-by-Step Slot Booking**: Choose doctor, verify real-time slot availability, provide patient contact details, select date and time, and confirm booking.
- **Double-Booking Prevention**: Automated server-side validation preventing overlapping appointment bookings for the same doctor and time slot.
- **Consultation Fee Payment Simulation**: Integrated Stripe Credit/Debit card and PayPal sandbox gateways generating verified transaction IDs and payment status logs.
- **Patient Dashboard**:
  - Overview of upcoming and past consultations.
  - Personal health history and digital vaccination records manager.
  - One-click cancellation for scheduled bookings.
  - System notifications and automated appointment confirmations.

#### 2.3 Doctor Portal
- **Doctor Overview**: Overview of consultation statistics, fees, and specialty metadata.
- **Weekly Schedule Manager**: Configure availability per day of week (Monday through Sunday), define shift start/end hours, slot durations, and toggle active availability.
- **Patient Appointment Roster**: Review patient appointment bookings, view patient contact and medical history, and update appointment status (`Confirmed`, `Completed`, `Cancelled`).
- **Profile Customization**: Update doctor qualifications, experience years, consultation fees, and biographical details.

#### 2.4 Administration Control Panel
- **System KPI Metrics**: Real-time counts of total doctors, registered patients, completed appointments, and total gateway revenue.
- **Master Cities Management (CRUD)**: Create, edit, and remove practice cities and territories.
- **Specialties Management (CRUD)**: Manage medical specialties, descriptions, and FontAwesome icons.
- **Doctor Management (CRUD)**: Create new doctor accounts with login credentials, edit doctor profiles, or delete doctor records.
- **Patient Records Oversight**: Review all registered patients, modify profile records, and view patient appointment frequency.
- **All Appointments & Gateway Logs**: Full system audit trail of all appointments, fee collections, payment methods, and status controls.
- **Content Management System**: Create, update, and remove health guides (Diseases, Preventions, Cures) and medical news articles.
- **Reports & Analytics**: Tabular reports with instant **CSV Export** for appointments and printed report capabilities.
- **System & Gateway Settings**: Configure clinic branding, emergency contacts, Stripe API keys, and PayPal credentials.

---

### 3. Security & Architecture Standards
- **SQL Injection Prevention**: 100% of database interactions execute through PDO prepared statements with parameter binding.
- **Role-Based Access Control (RBAC)**: Enforced via `require_role()` middleware, isolating `admin`, `doctor`, and `patient` capabilities.
- **Password Security**: Cryptographically secure hashing using `password_hash($pass, PASSWORD_BCRYPT)`.
- **Input Sanitization**: Global `sanitize_input()` helpers stripping harmful payloads and encoding special characters.
- **Atomic Transactions**: Multi-table operations (such as appointment booking and payment logging) wrapped in database transactions (`beginTransaction` / `commit` / `rollBack`).

---

### 4. Technical Stack
- **Backend**: PHP 7.4+ / 8.x with PDO MySQL
- **Database**: MySQL 5.7+ / 8.x (`medilo_db`)
- **Frontend**: HTML5, CSS3, Bootstrap 5, FontAwesome 6, jQuery, Slick Slider, WOW.js, Odometer.js
- **Design System**: Medilo Medical Responsive UI (Deep Navy `#002261`, Accent Cyan `#2a96e6`, Medical White `#ffffff`)

---
*Documentation generated automatically by Medilo Healthcare Management System.*
