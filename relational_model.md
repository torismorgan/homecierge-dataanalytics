# HomeCierge — Relational Database Model

## ERD to Relational Mapping

Each entity in the ERD maps to a relation (table). Foreign keys enforce all relationships identified in the ERD.

---

## Relations

---

### USERS
Stores all platform users. Role attribute distinguishes homeowners from contractors, eliminating the need for separate tables.

```
USERS (
    user_id     INT             PK  AUTO_INCREMENT,
    first_name  VARCHAR(50)     NOT NULL,
    last_name   VARCHAR(50)     NOT NULL,
    email       VARCHAR(100)    NOT NULL UNIQUE,
    phone       VARCHAR(20),
    address     VARCHAR(150),
    city        VARCHAR(80),
    state       VARCHAR(50),
    zip         VARCHAR(10),
    role        ENUM('homeowner','contractor')  NOT NULL,
    created_at  DATETIME        DEFAULT CURRENT_TIMESTAMP,
    is_active   BOOLEAN         DEFAULT TRUE
)
```

---

### SERVICE_CATEGORIES
Lookup table for service types. Shared by both listings and requests.

```
SERVICE_CATEGORIES (
    category_id  INT           PK  AUTO_INCREMENT,
    name         VARCHAR(80)   NOT NULL UNIQUE,
    description  VARCHAR(255)
)
```

---

### SERVICE_LISTINGS
Contractor-posted service offerings. Maps the ERD relationship "contractor posts listing".

```
SERVICE_LISTINGS (
    listing_id    INT             PK  AUTO_INCREMENT,
    contractor_id INT             FK → USERS(user_id)  ON DELETE CASCADE,
    category_id   INT             FK → SERVICE_CATEGORIES(category_id)  ON DELETE RESTRICT,
    title         VARCHAR(150)    NOT NULL,
    description   TEXT,
    price_min     DECIMAL(10,2),
    price_max     DECIMAL(10,2),
    location      VARCHAR(150),
    status        ENUM('active','inactive')  DEFAULT 'active',
    created_at    DATETIME        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT price_min <= price_max
)
```

---

### SERVICE_REQUESTS
Homeowner-posted job requests. Maps the ERD relationship "homeowner submits request".

```
SERVICE_REQUESTS (
    request_id   INT             PK  AUTO_INCREMENT,
    homeowner_id INT             FK → USERS(user_id)  ON DELETE CASCADE,
    category_id  INT             FK → SERVICE_CATEGORIES(category_id)  ON DELETE RESTRICT,
    description  TEXT            NOT NULL,
    location     VARCHAR(150),
    status       ENUM('open','in_review','closed')  DEFAULT 'open',
    created_at   DATETIME        DEFAULT CURRENT_TIMESTAMP
)
```

---

### QUOTES
Contractor responses to service requests. Maps the ERD relationship "contractor submits quote for request".

```
QUOTES (
    quote_id      INT             PK  AUTO_INCREMENT,
    request_id    INT             FK → SERVICE_REQUESTS(request_id)  ON DELETE CASCADE,
    contractor_id INT             FK → USERS(user_id)  ON DELETE CASCADE,
    amount        DECIMAL(10,2)   NOT NULL,
    message       TEXT,
    status        ENUM('pending','accepted','rejected')  DEFAULT 'pending',
    submitted_at  DATETIME        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT amount > 0
)
```

---

### BOOKINGS
Created when a homeowner accepts a quote. Maps the ERD relationship "accepted quote creates booking". The UNIQUE constraint on quote_id enforces the one-to-one relationship from ERD.

```
BOOKINGS (
    booking_id    INT             PK  AUTO_INCREMENT,
    quote_id      INT             FK → QUOTES(quote_id)  ON DELETE RESTRICT  UNIQUE,
    homeowner_id  INT             FK → USERS(user_id)  ON DELETE RESTRICT,
    contractor_id INT             FK → USERS(user_id)  ON DELETE RESTRICT,
    scheduled_at  DATETIME        NOT NULL,
    status        ENUM('scheduled','in_progress','completed','cancelled')  DEFAULT 'scheduled',
    created_at    DATETIME        DEFAULT CURRENT_TIMESTAMP
)
```

---

### REVIEWS
Written by homeowners after a completed booking. Maps the ERD relationship "completed booking generates review". The UNIQUE constraint on booking_id enforces the one-to-one relationship from ERD.

```
REVIEWS (
    review_id    INT        PK  AUTO_INCREMENT,
    booking_id   INT        FK → BOOKINGS(booking_id)  ON DELETE CASCADE  UNIQUE,
    reviewer_id  INT        FK → USERS(user_id)  ON DELETE CASCADE,
    reviewee_id  INT        FK → USERS(user_id)  ON DELETE CASCADE,
    rating       TINYINT    NOT NULL,
    comment      TEXT,
    created_at   DATETIME   DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT rating BETWEEN 1 AND 5,
    CONSTRAINT reviewer_id != reviewee_id
)
```

---

## Relationship Summary

| Relationship | Type | Enforced By |
|---|---|---|
| User posts Service Listing | 1:N | SERVICE_LISTINGS.contractor_id → USERS |
| User submits Service Request | 1:N | SERVICE_REQUESTS.homeowner_id → USERS |
| Category classifies Listing | 1:N | SERVICE_LISTINGS.category_id → SERVICE_CATEGORIES |
| Category categorizes Request | 1:N | SERVICE_REQUESTS.category_id → SERVICE_CATEGORIES |
| Request receives Quotes | 1:N | QUOTES.request_id → SERVICE_REQUESTS |
| Contractor submits Quote | 1:N | QUOTES.contractor_id → USERS |
| Quote creates Booking | 1:1 | BOOKINGS.quote_id → QUOTES (UNIQUE) |
| Booking generates Review | 1:1 | REVIEWS.booking_id → BOOKINGS (UNIQUE) |
| User writes Review | 1:N | REVIEWS.reviewer_id → USERS |
| User receives Review | 1:N | REVIEWS.reviewee_id → USERS |

---

## Integrity Constraints

| Constraint | Where | Rule |
|---|---|---|
| Price range valid | SERVICE_LISTINGS | price_min <= price_max |
| Positive quote amount | QUOTES | amount > 0 |
| Valid rating | REVIEWS | rating BETWEEN 1 AND 5 |
| No self-review | REVIEWS | reviewer_id != reviewee_id |
| One booking per quote | BOOKINGS | UNIQUE(quote_id) |
| One review per booking | REVIEWS | UNIQUE(booking_id) |
| Email uniqueness | USERS | UNIQUE(email) |
| Category name uniqueness | SERVICE_CATEGORIES | UNIQUE(name) |
