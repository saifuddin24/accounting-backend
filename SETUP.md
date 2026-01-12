# Backend Setup - Simple Accounting API

This project is built with Laravel and provides the RESTful API for the Simple Accounting system.

## Prerequisites

-   **PHP**: ^8.2
-   **Composer**
-   **MySQL** or **SQLite**
-   **Node.js & npm** (optional, for Vite if needed for backend assets)

## Installation Steps

1. **Clone the repository** (if not already done).
2. **Install Composer dependencies**:
    ```bash
    composer install
    ```
3. **Configure Environment**:
    - Copy `.env.example` to `.env`:
        ```bash
        cp .env.example .env
        ```
    - Update your database credentials in `.env`:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=api_simple_accounting
        DB_USERNAME=root
        DB_PASSWORD=your_password
        ```
4. **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```

## Database Setup

1. **Create the database**: Ensure you have created the database specified in your `.env` file.
2. **Run Migrations & Seeders**:
   This will create the necessary tables and populate initial data (Company, Fiscal Year, Chart of Accounts).
    ```bash
    php artisan migrate --seed
    ```
    _Note: This specific project uses `AccountingSeeder` to set up the default Chart of Accounts._

## Running the Server

To start the development server:

```bash
php artisan serve
```

By default, the API will be available at `http://localhost:8000`.

## Key Seeders

-   `AccountingSeeder`: Sets up the initial company, fiscal year, and Chart of Accounts.
-   `JournalEntrySeeder`: Populates sample journal entries for testing.
