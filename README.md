# Mallow Store Billing System

A Laravel-based store billing and inventory management mini-system built for the Mallow Technologies Laravel Developer mini-task.

## Features

- Product and customer management using Eloquent models.
- Database migrations, factories, and seeders for sample data.
- Create orders with one or more products.
- Validate customer details, product IDs, and quantities.
- Calculate line subtotals, tax, and grand totals using product prices and tax rates stored in the database.
- Check available inventory and deduct stock when an order succeeds.
- Use database transactions and row locking for safer stock updates.
- Retrieve order history by customer email.
- List low-stock products using a configurable threshold (default: 5).
- Queue a simulated order-confirmation email job and record the result in the Laravel log.
- Responsive billing page with product selection, payment amount, balance calculation, generated bill, and print option.
- Automated feature tests for order creation, insufficient stock, customer order history, and low-stock results.

## Tech Stack

- PHP 8.2+
- Laravel 12
- MySQL
- Blade templates and vanilla JavaScript
- Laravel database queue
- PHPUnit

## Requirements

- PHP and Composer
- MySQL
- Node.js and npm (if frontend asset tooling is needed)

## Setup

1. Clone the repository.
2. Install PHP dependencies:

    ```bash
    composer install
    ```

3. Copy `.env.example` to `.env` and configure the database connection.

    ```bash
    copy .env.example .env
    ```

    On macOS/Linux, use `cp .env.example .env`.

4. Generate the application key:

    ```bash
    php artisan key:generate
    ```

5. Create a MySQL database named `mallow_store` and update the database credentials in `.env`.

6. Run migrations and seed sample data:

    ```bash
    php artisan migrate --seed
    ```

7. Start the application:

    ```bash
    php artisan serve
    ```

8. Open the billing page at `http://127.0.0.1:8000/billing`.

## API Endpoints

| Method | Endpoint                                           | Purpose                                 |
| ------ | -------------------------------------------------- | --------------------------------------- |
| GET    | `/api/products`                                    | List products                           |
| GET    | `/api/products/low-stock?threshold=5`              | List products below the stock threshold |
| POST   | `/api/orders`                                      | Create an order                         |
| GET    | `/api/customers/orders?email=customer@example.com` | Retrieve customer order history         |

### Create an order

Example JSON request:

```json
{
    "customer": {
        "name": "Test Customer",
        "email": "customer@example.com"
    },
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```

Product prices and tax rates are read from the database rather than trusted from the request.

## Queue Worker

The confirmation job simulates sending an order-confirmation email by writing order and customer information to the Laravel log. No external email service is required.

For the database queue, run:

```bash
php artisan queue:work
```

Check `storage/logs/laravel.log` for the simulated confirmation message.

## Tests

Run the automated test suite:

```bash
php artisan test
```

The current test suite passes with 6 tests and 23 assertions.

## Assumptions and Notes

- Customer records are reused by email address.
- The default low-stock threshold is 5, and products are considered low-stock when stock is strictly below the threshold.
- Order prices and tax percentages are determined by current product records in the database.
- The generated bill is displayed in the browser and can be printed.
- The confirmation email is simulated through a queued job; real SMTP delivery is not configured.
- Actual concurrent stock locking should be validated using MySQL/InnoDB, because SQLite-based automated tests do not prove production database locking behavior.

## AI Assistance

AI assistance was used during development. The prompts used and relevant development records should be included in the `prompts/` directory as required by the task instructions.
