# **Appointment Booking System**  
![Drupal Module](https://img.shields.io/badge/Drupal-8%2B-0CA0FF) ![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-8892BF)

A robust appointment booking system for Drupal, enabling users to book, modify, and cancel appointments while managing agencies, advisers, and specializations. Includes administrative tools for managing bookings, agencies, and exporting data.

---

## **Table of Contents**
1. [Overview](#overview)
2. [Key Features](#key-features)
3. [Entity Types](#entity-types)
4. [User Interface](#user-interface)
5. [Administrative Interface](#administrative-interface)
6. [Installation](#installation)
7. [Configuration](#configuration)
8. [Usage](#usage)
9. [Contributing](#contributing)
10. [Support & Documentation](#support--documentation)
11. [License](#license)

---

## **Overview**  
This module provides a comprehensive appointment booking system with multi-step user workflows, agency/adviser management, and administrative tools. It supports role-based access control, email notifications, and CSV exports for bulk data management.

---

## **Key Features**  

### **1. Appointment Management**  
- **Book Appointments**: Users can book appointments via a multi-step form.  
- **Modify/Cancellations**: Users can edit or cancel their existing appointments.  
- **Double-Booking Prevention**: Time slots are dynamically checked for availability.  
- **CRUD Operations**: Administrators can create, edit, and delete appointments.  
- **Validation Rules**: Enforces date/time constraints and adviser availability.  

### **2. Specializations System**  
- **Taxonomy-Based**: Uses a vocabulary named **`appointment_type`** for managing specializations.  
- **Adviser Assignments**: Advisers must have at least one specialization (e.g., Career Counseling, Financial Advice).  
- **User Matching**: Users filter advisers by specialization during booking.  
- **Default Specializations**: Includes terms like:  
  - Career Counseling  
  - Financial Advice  
  - Legal Consultation  
  - Educational Guidance  
  - Health Services  

### **3. Agency & Adviser Management**  
- **Custom Agency Entity**: Manage agencies with fields like **name**, **address**, and **operating hours**.  
- **Adviser Roles**: Users with the **`adviser`** role have:  
  - **Working Hours**: Define available time slots (e.g., `09:00`, `10:00`).  
  - **Agency Reference**: Link advisers to specific agencies.  
- **Time Slot Logic**: Available slots are based on adviser schedules and booked appointments.  

### **4. User Interface**  
- **Multi-Step Booking Form**:  
  1. **Agency Selection**  
  2. **Specialization Filter**  
  3. **Adviser Selection**  
  4. **Date/Time Selection**  
  5. **Confirmation Step**  
- **Appointment Dashboard**: Users view and manage their bookings.  
- **Responsive Design**: Optimized for mobile and desktop use.  

### **5. Administrative Features**  
- **Appointment Listing**: Filter by date, agency, or adviser.  
- **CSV Export**: Export all appointments using batch processing.  
- **Agency/Adviser Management**: Add/edit agencies and configure adviser roles.  
- **Settings Page**: Configure global parameters like email templates or default durations.  

---

## **Entity Types**  

### **Appointment Entity**  
- **Fields**:  
  - Title  
  - Date & Time (`datetime`)  
  - Agency reference (`entity_reference`)  
  - Adviser reference (`entity_reference`)  
  - Customer details (name, email, phone)  
  - Status (Pending/Confirmed/Cancelled)  
  - Notes  
- **Routes**:  
  - `/admin/content/appointments` (List all appointments)  
  - `/appointment/{appointment}/edit` (Edit an appointment)  

### **Agency Entity**  
- **Fields**:  
  - Name  
  - Address  
  - Contact details  
  - Operating hours  
- **Route**: `/admin/structure/agencies`  

### **Adviser (User Extension)**  
- **Added Fields**:  
  - `field_agency` (Agency reference)  
  - `field_working_hours` (Time slots, e.g., `09:00`, `10:00`)  
  - `field_specializations` (Taxonomy terms from `appointment_type`)  

---

## **Administrative Interface**  

### **Key Routes**  
| Path | Purpose | Permission Required |  
|------|---------|---------------------|  
| `/admin/structure/appointment` | Global settings (email templates, defaults) | `administer appointment` |  
| `/admin/content/appointments` | List and filter all appointments | `administer appointment` |  
| `/admin/people/add-adviser` | Create advisers with agency/specialization links | `administer users` |  
| `/admin/appointment/export` | Export appointments to CSV | `administer site configuration` |  

### **Features**  
- **Appointment Listing**: View all bookings with status, date, and customer details.  
- **CSV Export**: Export data for reporting or backups.  
- **Agency Management**: Add/edit agencies via the **Agencies** admin section.  

---

## **Installation**  

1. **Enable the Module**:  
   ```bash  
   drush en appointment -y  
   ``` 
2.**Rebuild Permissions**:  
   ```bash  
   drush cr  
   ```  

---

## **Configuration**  

### **1. Setup Agencies**  
- Go to **Structure > Agencies** (`/admin/structure/agencies`).  
- Add agencies with addresses and operating hours.  

### **2. Configure Specializations**  
- Go to **Structure > Taxonomy > Appointment Type** (`/admin/structure/taxonomy/appointment_type`).  
- Add/edit terms like **Career Counseling** or **Financial Advice**.  

### **3. Create Advisers**  
- Use `/admin/people/add-adviser` to add advisers.  
- Assign agencies, working hours, and specializations.  

### **4. Global Settings**  
- Configure email templates, default durations, and other parameters at `/admin/structure/appointment`.  

---

## **Usage**  

### **For Users**  
1. Visit **Book an Appointment** at `/appointment/book`.  
2. Follow the 5-step form to select an agency, specialization, adviser, date/time, and confirm details.  
3. Modify/cancel appointments via the **My Appointments** dashboard.  

### **For Administrators**  
1. Manage agencies and advisers via `/admin/structure/agencies` and `/admin/people`.  
2. List/export appointments at `/admin/content/appointments` and `/admin/appointment/export`.  
3. Adjust settings at `/admin/structure/appointment`.  

---

## **Contributing**  

1. **Development Environment**:  
   - Clone the repo.  
   - Install dependencies: `composer install`.  
   - Use **Drupal Console** or **Drush** for local development.  

2. **Code Standards**:  
   - Follow [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards).  
   - Validate with `drush phpcs`.  

3. **Testing**:  
   - PHPUnit tests for core functionality (located in `/tests`).  
   - Use [Mailhog](https://github.com/mailhog/Mailhog) for email testing.  

4. **Pull Requests**:  
   - Ensure tests pass and code is formatted.  
   - Document changes in `CHANGELOG.md`.  

---

## **Support & Documentation**  

- **Documentation**: [Module Wiki](https://github.com/your-username/appointment/wiki)  
- **Issues**: Report bugs or feature requests on [GitHub Issues](https://github.com/your-username/appointment/issues)

---

### **French Localization**  
- Fully supports French labels and messages.  
- Translations are included in the module’s configuration.  

---

### **Dependencies**  
- **Core Modules**: Field UI, Taxonomy, User, Views.
---

### **Getting Started Guide**  
1. **Install Agencies**: Add agencies via `/admin/structure/agencies`.  
2. **Assign Advisers**: Create advisers with `/admin/people/add-adviser`.  
3. **Test Booking**: Visit `/appointment/book` to simulate a booking.  
4. **Export Data**: Use `/admin/appointment/export` for CSV exports.  

---

### **Roadmap**  
- [ ] Add recurrence support for appointments.  
- [ ] Integrate FullCalendar.io for visual scheduling.  
- [ ] Implement soft-deletion for cancelled appointments.  

---

### **Authors & Credits**  
- **Primary Maintainer**: Massir Hamza
- **Inspired By**: Drupal core’s entity system and best practices.  

