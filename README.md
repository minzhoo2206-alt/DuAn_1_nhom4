# FOURTECH Phone Store

## Run locally

1. Install dependencies with `composer install`.
2. Copy `.env.example` to `.env` and set the database connection values.
3. Start the PHP development server from the project root:

   ```sh
   php -S 127.0.0.1:8000 -t public public/index.php
   ```

4. Open `http://127.0.0.1:8000/` for the storefront or `/health` to check the PHP application.

The existing storefront pages are in `VIew/Pages`, admin pages are in `admin`, and styles/scripts are in `assets`. The pages are currently static HTML; authentication, checkout, and administration actions are not connected to backend handlers.

## Tests

Run the existing unit tests with `composer test`.
