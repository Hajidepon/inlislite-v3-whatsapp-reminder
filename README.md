# WhatsApp Reminder for Inlislite V3

Simple Proof of Concept (PoC) application to send WhatsApp reminders for overdue library loans from Inlislite V3 database using CodeIgniter 3 and Fonnte API.

## Features

-   **Database Integration**: Connects directly to existing `inlislite_v3` MySQL database.
-   **Overdue Detection**: Automatically queries loans that are due today or overdue.
-   **WhatsApp Notification**: Sends personalized reminder messages via Fonnte API.
-   **Safe Sending**: Includes delay mechanism to prevent WhatsApp blocking.

## Requirements

-   PHP 5.6 (Compatible with Inlislite V3 environment)
-   CodeIgniter 3 (Included)
-   MySQL Database (Inlislite V3 Schema)
-   Fonnte API Token (Free/Premium)

## Installation

1.  Clone this repository.
2.  Run `composer install` to install dependencies (phpdotenv).
3.  Copy `.env.example` to `.env`.
4.  Configure your `.env` file:
    ```ini
    FONNTE_TOKEN=your_fonnte_token_here
    DB_HOST=localhost
    DB_USER=root
    DB_PASS=
    DB_NAME=inlislite_v3
    DB_PORT=3306
    ```

## Usage

### 1. Generate Dummy Data (Testing Only)
Access the seeder URL to populate test members and loans:
`http://localhost/reminder-whatsapp/index.php/seeder/run`

### 2. Run Reminder Process
Access the reminder URL to check for overdue items and send messages:
`http://localhost/reminder-whatsapp/index.php/reminder/process`

## License

MIT License
