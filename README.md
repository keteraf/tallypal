# Tallypal API Backend

A Laravel-based REST API backend for **Tallypal**, a personal finance tracker that allows you to share tallies (financial records) with friends, family, or anyone you want.

## Overview

Tallypal is a collaborative personal finance management system where users can:
- Create and manage financial tallies
- Share tallies with other users
- Track income and expense transactions
- Organize transactions by categories and currencies
- Manage shared percentages for collaborative expenses

## Tech Stack

- **PHP**: 8.4
- **Laravel**: 12
- **Database**: SQLite (default)
- **Authentication**: Laravel Sanctum 4
- **API Resources**: Laravel API Resources for JSON responses
- **Testing**: Pest 4
- **Code Quality**: Laravel Pint, PHPStan, Rector

## Requirements

- PHP 8.4 or higher
- Composer
- Node.js and npm (for frontend assets)
- SQLite (or MySQL/PostgreSQL for production)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd tallypal
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install frontend dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   # For SQLite (default)
   touch database/database.sqlite
   
   # Or configure MySQL/PostgreSQL in .env
   php artisan migrate
   ```

6. **Build frontend assets** (if needed)
   ```bash
   npm run build
   ```

## API Overview

The API is RESTful and uses Laravel Sanctum for authentication. All authenticated endpoints require a Bearer token in the `Authorization` header.

### Base URL

All API endpoints are prefixed with `/api`:

```
http://your-domain.com/api
```

### Authentication

The API uses Laravel Sanctum for token-based authentication:

1. **Register**: `POST /api/register`
2. **Login**: `POST /api/login`
3. **Get User**: `GET /api/user` (requires authentication)
4. **Logout**: `POST /api/logout` (requires authentication)

After login/register, include the token in requests:
```
Authorization: Bearer {token}
```

### Main Resources

The API provides the following main resource endpoints:

- **Tallies** (`/api/tallies`): Manage financial tallies
- **Transactions** (`/api/tallies/{tally}/transactions`): Manage transactions within a tally
- **Tally Shares** (`/api/tallies/{tally}/shares`): Manage user shares for a tally

All resource endpoints follow RESTful conventions (GET, POST, PUT, PATCH, DELETE).

## Database Schema

### Core Tables

- **users**: User accounts
- **tallies**: Financial tallies (owned by users)
- **tally_user**: Pivot table for shared tallies with default share percentages
- **transactions**: Income and expense transactions
- **categories**: Transaction categories
- **currencies**: Supported currencies

### Relationships

- A `Tally` belongs to a `User` (owner)
- A `Tally` can be shared with multiple `User`s (many-to-many)
- A `Transaction` belongs to a `Tally`, `User` (party), `Category`, and `Currency`
- Transactions can be either `income` or `expense` type

## Development

### Running the Development Server

```bash
composer run dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server

### Code Quality

**Linting:**
```bash
composer run lint
```

**Type Checking:**
```bash
composer run test:types
```

**Refactoring Check:**
```bash
composer run test:refactor
```

**Run All Checks:**
```bash
composer run test
```

### Testing

Run tests with Pest:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# Run with coverage
php artisan test --coverage
```

The test suite includes:
- Feature tests for API endpoints
- Unit tests for actions, DTOs, and models
- Authentication and authorization tests

## Key Features

### Tally Management
- Create, read, update, and delete tallies
- Each tally has an owner and can be shared with multiple users
- Shared users have configurable default share percentages

### Transaction Tracking
- Track income and expense transactions
- Associate transactions with categories and currencies
- Link transactions to specific parties (users)
- Filter and paginate transactions

### Sharing System
- Share tallies with other users by email
- Update share percentages for collaborative expenses
- Remove users from shared tallies

## License

MIT

## Contributing

1. Follow the existing code style (enforced by Laravel Pint)
2. Write tests for new features
3. Ensure all tests pass before submitting
4. Follow Laravel best practices and conventions
