# HomeCierge — Home Services Management System

A web-based database application that connects homeowners with local contractors for home service needs. Built with PHP and MySQL.

## What it does

- Homeowners post service requests and receive quotes from contractors
- Contractors post service listings and respond to requests
- Bookings are created when a quote is accepted
- Homeowners leave reviews after a job is completed
- Dashboard shows live stats across all platform activity

## Pages

| Page | Description |
|---|---|
| Dashboard | Overview of all activity — users, requests, bookings |
| Submit a Request | Homeowner posts a new service job |
| Find Contractors | Search by category, location, and budget |
| Manage Bookings | Update booking status as work progresses |
| Manage Listings | View and remove contractor service listings |

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Local server:** XAMPP (Apache + MySQL)

## How to run it locally

### 1. Install XAMPP
Download from [apachefriends.org](https://www.apachefriends.org) and start both **Apache** and **MySQL**.

### 2. Clone the repo
```bash
cd /Applications/XAMPP/htdocs
git clone https://github.com/torismorgan/homecierge-dataanalytics.git
cd homecierge-dataanalytics
```

### 3. Set up your config file
```bash
cp config.example.php config.php
```
Open `config.php` and set your credentials — XAMPP defaults are:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'homecierge');
```

### 4. Set up the database
1. Open `http://localhost/phpmyadmin`
2. Create a new database called `homecierge`
3. Click the **SQL** tab, paste the contents of `schema.sql`, click **Go**
4. Repeat with `seed.sql` to load sample data

### 5. Open the app
```
http://localhost/homecierge-dataanalytics/index.php
```

## Database Structure

7 tables: `users`, `service_categories`, `service_listings`, `service_requests`, `quotes`, `bookings`, `reviews`

See [`relational_model.md`](relational_model.md) for the full schema and relationship mapping.
