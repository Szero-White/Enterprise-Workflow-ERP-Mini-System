# Enterprise Workflow & ERP Mini System

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Tests](https://img.shields.io/badge/tests-17%20passed-success)](#automated-tests)
[![Assertions](https://img.shields.io/badge/assertions-61-success)](#automated-tests)

A Laravel-based internal workflow management system for configurable business approval processes.

The application provides dynamic forms, multi-step approvals, role-based authorization, notifications, audit logging, file attachments, approval history, dashboard statistics, and automated feature testing.

The current demo focuses on a leave-request workflow and is structured so the same workflow engine can support additional internal processes such as purchase requests, payment requests, business trips, and document approvals.

---

## Live Demo

**Application:** https://workflow-erp.alwaysdata.net

### Demo Accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| Manager | `manager@example.com` | `password` |
| Employee | `employee@example.com` | `password` |
| HR | `hr@example.com` | `password` |
| Director | `director@example.com` | `password` |

---

## Tech Stack

**Backend**
- PHP 8.2+
- Laravel 12
- Eloquent ORM
- Laravel Session Authentication
- Laravel Notifications
- Laravel Validation
- Database Transactions

**Frontend**
- Blade
- Bootstrap 5
- HTML / CSS
- JavaScript
- AJAX for selected interactions
- Vite

**Database**
- SQLite for local development/demo
- MySQL configuration supported

**Testing & Tooling**
- PHPUnit
- Laravel Feature Tests
- Composer
- npm
- Git
- Vite

---

## Architecture

```text
HTTP Request
    ↓
Controller
    ↓
FormRequest
    ↓
Service Layer
    ↓
Eloquent Models
    ↓
Database
```

### Responsibilities

**Controllers**
- Receive HTTP requests
- Call application services
- Return Blade views or redirects
- Keep business rules out of controllers

**Form Requests**
- Validate request payloads
- Define request-specific validation rules
- Stop invalid data before it reaches the service layer

**Service Layer**
- Submit workflow requests
- Process approval actions
- Transition workflow steps
- Create notifications
- Persist approval history
- Record audit logs
- Coordinate database transactions

**Models**
- Define Eloquent relationships
- Represent request and workflow state
- Store domain metadata and persisted data

---

## Core Modules

### Authentication & Authorization
- Session-based login/logout
- Role-based access control
- Separate access levels for Admin, Employee, Manager, HR, and Director
- Resource ownership checks
- Current-step approval authorization

### Organization Management
- Role management
- Department management
- User management
- User assignment by department and role

### Dynamic Form Management

Administrators can create reusable form templates without hard-coding a dedicated request form for every business process.

Supported field types:
- Text
- Textarea
- Number
- Date
- Select
- File upload

Request metadata and dynamic field values are stored separately so the same request engine can support multiple form types.

### Workflow Management
- Workflow templates
- Workflow steps
- Sequential approval flow
- Current-step tracking
- Approve / Reject / Return actions
- Required comments for Reject and Return

### Employee Portal
Employees can:
- Select an available form template
- Submit a request
- Upload attachments
- View their own requests
- Track request status
- Review approval progress
- Receive notifications

### Approval Center
Approvers can:
- View requests assigned to their current step
- Review request details
- Approve requests
- Reject requests
- Return requests for correction
- View approval history
- Filter completed approval actions

### Notifications
- Laravel database notifications
- Mark one notification as read
- Mark all notifications as read
- Notification ownership protection
- Employee notification after workflow completion
- Optional external realtime notification endpoint

### Approval History
Each approval action records:
- Request
- Workflow step
- Approver
- Action
- Comment
- Action timestamp

### Audit Log
Important operations may record:
- Acting user
- Action description
- Previous values
- New values
- Related request
- Timestamp

### Dashboard
- Request statistics
- Workflow status summaries

### Search, Filtering & Pagination
- Search
- Filtering
- Pagination

---

## Approval Workflow

```text
Employee
   ↓ Submit
Manager
   ↓ Approve
HR
   ↓ Approve
Director
   ↓ Approve
Approved
   ↓
Employee Notification
```

The same workflow engine can be reused for:
- Leave Request
- Purchase Request
- Payment Request
- Business Trip Request
- Document Approval

---

## Workflow States

```text
pending
approved
rejected
returned
```

Workflow progress is tracked separately through the request's current workflow step.

---

## Data Integrity & Authorization

Important workflow operations are executed inside database transactions so related changes remain consistent.

A typical approval action can update:
- Request status
- Current workflow step
- Approval history
- Audit log
- Notification

The application also prevents:
- Approval by users outside the active workflow step
- Re-approval of completed requests
- Employees accessing restricted administration pages
- Employees viewing requests owned by other employees
- Users modifying notifications that do not belong to them

---

## Main Domain Flow

```text
Employee submits request
        ↓
Request values persisted
        ↓
Initial workflow step assigned
        ↓
Approver notified
        ↓
Approver performs action
        ↓
Service validates current step
        ↓
Database transaction
        ↓
Request state updated
        ↓
Approval history persisted
        ↓
Audit log persisted
        ↓
Next workflow step selected
        ↓
Next approver notified
        ↓
Final approval
        ↓
Employee notified
```

---

## Localization

```env
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Ho_Chi_Minh
```

Internal status keys remain in English while user-facing labels are translated through Laravel language files.

---

## Local Development

### Requirements
- PHP 8.2+
- Composer
- Node.js
- npm

```bash
git clone https://github.com/Szero-White/Enterprise-Workflow-ERP-Mini-System.git
cd Enterprise-Workflow-ERP-Mini-System

composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link

npm install
npm run build

php artisan migrate:fresh --seed
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

---

## Database Configuration

### SQLite

```env
DB_CONNECTION=sqlite
```

Database file:

```text
database/database.sqlite
```

### MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enterprise_workflow_erp
DB_USERNAME=root
DB_PASSWORD=
```

---

## Optional Realtime Notification Integration

```env
NODE_NOTIFICATION_URL=http://127.0.0.1:3001/api/notify
NODE_NOTIFICATION_TIMEOUT=2
```

If the external endpoint is unavailable:
- Workflow processing continues normally
- Database notifications are still persisted
- The failure is logged as a warning

The realtime service is not required for the core workflow system.

---

## Automated Tests

```bash
php artisan test
```

Current local result:

```text
17 tests passed
61 assertions
```

Coverage includes:
- Authentication redirects
- Login page rendering
- Employee request submission
- Manager notification after submission
- Manager approval transition
- HR approval transition
- Director approval completion
- Employee notification after completion
- Reject validation
- Return validation
- Wrong-step authorization
- Completed-request protection
- Admin-page authorization
- Request ownership protection
- Notification ownership protection
- Approval history persistence
- Audit log persistence
- Realtime service failure handling

---

## Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
└── Services/

database/
├── migrations/
└── seeders/

lang/
└── vi/

resources/
└── views/

routes/
└── web.php

tests/
├── Feature/
└── Unit/
```

---

## Current Demo Scope

The current demo focuses on the workflow engine through a leave-request process.

Planned request templates:
- Purchase Request
- Payment Request
- Business Trip Request
- Document Approval

---

## Roadmap

- Additional request templates
- Improved dashboard analytics
- Export to Excel/CSV
- REST API
- Email notifications
- Queue-based notification processing
- Docker Compose
- CI/CD pipeline
- Production deployment
- Optional Socket.IO realtime notification service

---

## Repository

**GitHub:** https://github.com/Szero-White/Enterprise-Workflow-ERP-Mini-System
