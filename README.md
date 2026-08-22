# Pharmacovigilance API

Laravel 13 REST API for finding buyers of a medication lot and sending recall warnings. It uses MySQL, Sanctum stateful authentication, Laravel Resources, Pest, and Laravel Sail with PHP 8.5 and Mailpit.

The Vue frontend is maintained in the separate `pharmacovigilance-front` repository.

## Requirements

- Docker Engine with Docker Compose
- Ports `8000`, `3308`, and `8025` available, or equivalent overrides in `.env`

No host PHP, Composer, Node.js, or MySQL installation is required.

## Setup instructions

Clone the repository and enter the project directory:

```bash
git clone https://github.com/kwgeniusz/pharmacovigilance-api.git
cd pharmacovigilance-api
```

Create the local environment file, build the PHP 8.5 image, and install Composer dependencies inside the container:

```bash
cp .env.example .env
docker compose build laravel.test
docker compose run --rm laravel.test composer install
docker compose up -d
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate:fresh --seed
```

After Composer has created `vendor/`, the Sail helper can be used for subsequent commands.

The services are available at:

- API: `http://localhost:8000`
- Mailpit inbox: `http://localhost:8025`
- MySQL: `localhost:3308`

Stop the environment with `./vendor/bin/sail down` or `docker compose down`.

## Seeded data

- Administrator username: `admin`
- Administrator password: `password`
- Operator username: `operator`
- Operator password: `password`
- Affected medication lot: `951357`
- Purchases of that lot inside the rolling 30-day window
- An older purchase of the same lot
- A recent purchase of an unrelated lot

The seeded dates are calculated when the seeder runs, so the rolling-window examples remain valid.

## Stateful authentication

Sanctum authenticates the browser SPA with the Laravel session cookie. The expected local frontend origin is `http://localhost:5173`; it can be changed through `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS`.

A browser client must call `GET /sanctum/csrf-cookie`, log in, and then send its session cookie and XSRF header with protected requests.

```js
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
})

await api.get('/sanctum/csrf-cookie')
await api.post('/api/login', { username: 'admin', password: 'password' })
const orders = await api.get('/api/orders', { params: { lot_number: '951357' } })
```

## API endpoints

| Method | Endpoint | Authentication | Description |
| --- | --- | --- | --- |
| `GET` | `/sanctum/csrf-cookie` | No | Initialize CSRF protection. |
| `POST` | `/api/login` | No | Start an authenticated session. |
| `POST` | `/api/logout` | Yes | End the current session. |
| `GET` | `/api/user` | Yes | Return the authenticated user and role. |
| `GET` | `/api/medications/search` | Yes | Find medication records by exact lot number. |
| `GET` | `/api/orders` | Yes | Find and paginate affected orders. |
| `GET` | `/api/orders/export` | Administrator | Download all affected orders matching the filters as CSV. |
| `GET` | `/api/orders/{order}` | Yes | Return an order with customer and medication details. |
| `GET` | `/api/customers/{customer}` | Yes | Return a customer and their order history. |
| `POST` | `/api/alerts/send` | Yes | Email the buyer of an order affected by a lot. |

### Search parameters

`lot_number` is required for medication and order searches. The order endpoint also accepts inclusive `start_date` and `end_date` values in `YYYY-MM-DD` format. When omitted, the range is the rolling 30 days ending today. A reversed range returns `422 Unprocessable Entity`.

```http
GET /api/orders?lot_number=951357&start_date=2026-07-22&end_date=2026-08-21&page=1
Accept: application/json
```

Paginated responses contain `data`, `links`, and `meta`. Orders are sorted by newest `purchase_date` and include customer contact data and medications matching the requested lot.

### Export affected orders

Administrators can export every order matching the current lot and date filters. The streamed CSV is not limited to the visible pagination page.

```http
GET /api/orders/export?lot_number=951357&start_date=2026-07-22&end_date=2026-08-21
Accept: text/csv
```

The CSV contains the order ID, customer contact information, purchase date, medication, and lot number. Values that could be interpreted as spreadsheet formulas are escaped. Operators receive `403 Forbidden`.

## Postman collection

Import [`docs/pharmacovigilance-api.postman_collection.json`](docs/pharmacovigilance-api.postman_collection.json) into Postman and run the complete collection in its numbered order. No separate Postman environment is required.

The collection uses `http://localhost:8000`, the seeded administrator, and lot `951357`. It initializes CSRF protection, refreshes the token after login, preserves the Laravel session, adds the XSRF header, calculates a rolling 30-day range, and captures the matching order and customer IDs. It also tests expected `422` responses. The successful alert email appears at `http://localhost:8025`.

Before running it:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
```

### Send an alert

```http
POST /api/alerts/send
Accept: application/json
Content-Type: application/json

{
  "order_id": 1,
  "lot_number": "951357"
}
```

The recipient and medication details are always resolved from the database. A request is rejected with `422` when the selected order does not contain the requested lot. Successful local messages appear in Mailpit.

## Response conventions

- Resources are wrapped in `data`.
- Paginated resources include `data`, `links`, and `meta`.
- Unauthenticated requests return `401`.
- Missing model records return `404`.
- Invalid input and business-rule violations return `422` with an `errors` object.

## Design decisions

### Architecture

The project follows Laravel MVC with a small application layer:

```text
app/
├── Actions/Alerts/       # Alert validation and delivery workflow
├── Http/
│   ├── Controllers/Api/ # Thin HTTP coordinators
│   ├── Requests/        # Validation and input normalization
│   └── Resources/       # Stable REST response shapes
├── Mail/                # Email construction
├── Models/              # Eloquent persistence and relationships
└── Queries/Orders/      # Reusable order filtering
```

The project does not use the Repository Pattern or a JSON:API dependency. Models handle Eloquent behavior, controllers coordinate requests, query objects filter orders, and actions send alerts.

Both roles can search, view records, and send individual alerts. Only administrators can export CSV results. Backend middleware blocks operators from the export endpoint.

## Quality checks

```bash
./vendor/bin/sail pint --test
./vendor/bin/sail artisan test
./vendor/bin/sail artisan route:list --path=api
```

The Pest suite covers authentication, role authorization, validation, inclusive date filtering, the default rolling window, pagination, CSV export, detail responses, missing records, and mail delivery. Date-sensitive tests freeze time.

## Email configuration

Local SMTP points to the `mailpit` Compose service on port `1025`. Other environments can replace the standard Laravel `MAIL_*` variables without changing application code.

## Assumptions

- “Last month” means a rolling 30-day period, including both boundary dates.
- Authentication is stateful and intended for the first-party Vue SPA.
- The administrator and operator roles are sufficient; user management is outside scope.
- Only orders containing the requested lot can receive a recall alert.
- CSV export and role-based access are implemented. Bulk alerts, SMS, and alert audit storage are excluded.
