## Installation

Follow these steps to get the API running locally:

1. Install the API scaffolding:
    ```bash
    php artisan install:api
    ```
2. Install and publish Laravel Sanctum:
    ```bash
    composer require laravel/sanctum
    php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
    ```
3. Run database migrations:
    ```bash
    php artisan migrate
    ```

After completing the steps above, you can authenticate using Sanctum tokens and interact with the `/api` endpoints.
