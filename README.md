# Leave Application System

## Description
A web-based Internal Leave Application System specifically intended for the MITCS DepartmenT.

## Getting Started

### Dependencies
The following dependencies are required to run the system:
- [PHP 8.5.2+, Laravel Framework 12.48.1+, Composer 2.9.4+](https://php.new)
- Local Server Environment (preferably [Laragon](https://laragon.org/download))
- [Commitizen](https://github.com/commitizen/cz-cli)
- [NodeJS](https://nodejs.org/en/download) & [NPM](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)
- [GitHub CLI](https://cli.github.com/)

### Installing
**Ensure all dependencies are installed before proceeding.**

1. Clone the repository:
```bash
    git clone https://github.com/sPatrick2302175/mitcs-project.git
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

