# 🧮 TallyPal

> Track money. Share tallies. Stay in sync.

A personal finance tracker with a native web frontend for immediate use and a REST API for third-party and native app integrations. Create tallies, log transactions, invite collaborators, and split expenses — all in one place.

---

## ✨ Features

- 📒 **Tallies** — shared ledgers you own and control
- 💸 **Transactions** — log income and expenses per tally, with category and currency
- 🤝 **Sharing** — invite users by email, configure custom split percentages per member
- 🔌 **API** — full REST API for external integrations and native clients

---

## 🛠 Stack

| Layer         | Tech                                               |
|---------------|----------------------------------------------------|
| Backend       | PHP 8.4, Laravel 13, Sanctum, Spatie Permissions   |
| Frontend      | Vue 3, TypeScript, Inertia.js v2, Tailwind CSS v4  |
| UI            | Reka UI, Lucide, Vue I18n                          |
| Routing       | Wayfinder (typed route bindings)                   |
| Testing       | Pest 4, PHPStan, Rector, Pint                      |
| Observability | Telescope, Pulse, Nightwatch                       |

---

## 🚀 Getting Started

```bash
git clone <repo> && cd tallypal
composer install && npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
npm run build
```

Start everything at once (Laravel + Vite + queue worker + log viewer):

```bash
composer run dev
```

---

## 📁 Project Structure

```
app/
├── Actions/       # Single-purpose business logic
├── DTOs/          # Typed data transfer objects
├── Enums/         # PHP 8.1+ enums
├── Http/
│   └── Controllers/   # Inertia pages + API endpoints
├── Models/
├── Policies/
├── Repositories/
└── Services/

resources/js/
├── pages/         # Inertia Vue pages
├── components/    # Shared Vue components
└── layouts/
```

---

## 📐 Conventions

- **Actions** over fat controllers — one class, one job
- **DTOs** for structured data across layers
- **Repositories** for data access
- **Wayfinder** for frontend routes — import from `@/actions/` or `@/routes/`, never hardcode URLs
- **Inertia** for all UI responses — no Blade templates, no manual JSON for page data
- Named routes everywhere via `route()` helper
- PHP 8 constructor property promotion + explicit return types throughout

---

## ✅ Code Quality

```bash
composer test              # full suite: lint + types + refactor + tests + mutation tests
composer run lint          # auto-format with Pint
composer run test:types    # PHPStan static analysis
composer run test:unit     # Pest with coverage (≥90% required)

npm run lint               # Prettier + ESLint fix
```

---

## 📄 License

MIT
