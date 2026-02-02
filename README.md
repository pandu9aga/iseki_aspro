# Iseki Aspro - Audit & Procedure Management System

## Overview

**Iseki Aspro** is a comprehensive Web-based Application designed for managing audit procedures, reporting, and team coordination. It features a robust role-based access control system tailored for Leaders, Auditors, and Members to streamline the workflow of tractor/area procedures, finding (temuan) management, and performance reporting.

## Key Features

The application is divided into three main modules based on user roles:

### 1. Leader (Admin/Manager)
*   **Dashboard**: Overview of system status and metrics.
*   **User & Team Management**:
    *   Full CRUD for Users.
    *   Team creation and Member assignment.
    *   Bulk Member Import functionality.
*   **Procedure Management**:
    *   Manage Tractors, Areas, and specific Procedures.
    *   Upload and update procedure documents/items.
    *   Track missing procedures and assign training.
*   **Report Management**:
    *   Generate and manage monthly reports.
    *   Review submitted reports from members/auditors.
    *   Create monthly report templates.
*   **Findings (Temuan) Management**:
    *   Monitor and manage audit findings.
    *   Track statistics (Monthly, Missing).
    *   Handle "Penanganan" (Handling/Resolution) of findings.

### 2. Auditor (Reviewer)
*   **Report Auditing**:
    *   View and validate reports submitted by teams.
    *   Detailed view of reports by Tractor/Area.
*   **Findings Verification**:
    *   Validate findings ("Temuan") reported.
    *   Create and submit new audit findings.
    *   Access statistical data on findings.
*   **Profile Management**: Manage personal profile settings.

### 3. Member (Field User)
*   **Report Submission**:
    *   Submit daily/periodic reports.
    *   Upload photos as evidence for reports.
*   **Profile Management**: Update personal information.

## Technology Stack

### Backend
*   **Framework**: [Laravel 12.x](https://laravel.com)
*   **Language**: PHP ^8.2
*   **Database**: SQLite (Default) / MySQL Compatible
*   **Excel Processing**: `maatwebsite/excel`, `phpoffice/phpspreadsheet`

### Frontend
*   **Build Tool**: [Vite](https://vitejs.dev)
*   **Styling**: [Tailwind CSS v4.0](https://tailwindcss.com)
*   **HTTP Client**: Axios

## Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone <repository-url>
    cd iseki_aspro
    ```

2.  **Install Dependencies**
    *   PHP Dependencies:
        ```bash
        composer install
        ```
    *   Node/JS Dependencies:
        ```bash
        npm install
        ```

3.  **Environment Configuration**
    *   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    *   Configure your database and other settings in `.env`.

4.  **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

5.  **Run Migrations**
    ```bash
    php artisan migrate
    ```
    *   *(Optional) Seed the database if seeders are available:*
        ```bash
        php artisan db:seed
        ```

6.  **Build Frontend Assets**
    ```bash
    npm run build
    # Or for development:
    npm run dev
    ```

7.  **Serve the Application**
    ```bash
    php artisan serve
    ```
    The application will be available at `http://localhost:8000`.

## Project Structure

*   **`app/Http/Controllers`**: Contains logic separated by role (`Auditor`, `Leader`, `Member`).
*   **`routes/web.php`**: Defines all web routes, grouped by Middleware (`AuditorMiddleware`, `LeaderMiddleware`, `MemberMiddleware`).
*   **`resources/views`**: Frontend Blade templates.

## License

This project is proprietary. Please check the license file for details.
