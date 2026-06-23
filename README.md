# NGC - Leave Application System

## Description
A web-based Automated Employee Leave Management & Compliance System specifically intended for the MITCS Department. The system streamlines organizational workflows by handling role-based approvals, dynamic leave ledgers, interactive holiday calendars, and automated government-compliant (CSC Form 6) PDF generation.

##  Core Features
* **Role-Based Access Control (RBAC):** Dynamic isolation of privileges between standard Employees, Admin Officers, Department Heads, and Super Admins.
* **Intelligent Leave Application Engine:** Multi-date selection (via Flatpickr), automatic valid working day calculation, and strict 5-day advance filing compliance checks.
* **Hierarchical Approval Workflow:** Multi-tier processing requiring mandatory textual justifications for disapprovals and tracking of paid vs. unpaid days.
* **Polymorphic Leave Ledger:** Bulletproof financial tracking of leave balances, mass monthly accruals, and annual resets.
* **Interactive Calendar (FullCalendar API):** Visual timeline mapping for pending/approved leaves and customizable regular/half-day holidays.
* **Automated PDF Generator:** Server-side rendering (via FPDI) that compiles live employee metrics and chronological date groupings directly into official physical forms.

## Getting Started

### Dependencies
The following dependencies are required to run the system:
- [PHP 8.5.2+, Laravel Framework 12.48.1+, Composer 2.9.4+](https://php.new)
- Local Server Environment (preferably [Laragon](https://laragon.org/download))
- [NodeJS](https://nodejs.org/en/download) & [NPM](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)
- [GitHub CLI](https://cli.github.com/)

### Installing
**Ensure all dependencies are installed before proceeding.**

1. Clone the repository:
```bash
    git clone https://github.com/spatrickjs/mitcs-project.git
    cd mitcs-project
```

2. Install PHP dependencies:
```bash
    composer install
    composer require setasign/fpdf setasign/fpdi
```

3. Install frontend dependencies:
```bash
    npm install
```

4. Create local environment setup:
```bash
    cp .env.example .env
    php artisan key:generate
```

5. Initialize the database:
```bash
    php artisan migrate --seed
```

Instead of `php artisan serve`, use:
```bash
   composer run dev
```

## Database ERD

Raw script file located at: `docs/database/schema.dbml`

