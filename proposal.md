# HomeCierge — Project Proposal

**Project Title:** HomeCierge — Homeowner & Contractor Service Management System
**Platform:** Web-based Database Application
**Database:** MySQL
**Backend:** PHP
**Team:** Victoria Morgan

---

## 1. Application Overview

HomeCierge is a Dallas-based web platform that connects homeowners with local contractors
for home service needs. The application allows homeowners to post service requests, receive
quotes from contractors, book jobs, and leave reviews. Contractors can post service listings,
respond to requests with quotes, and manage their bookings.

The goal of this application is to centralize and manage all service-related data in a
structured relational database, providing a clean interface for all parties to interact
with that data through a set of well-defined operations.

---

## 2. Data the Application Will Be Managing

The application manages data across seven core areas:

---

### 2.1 Users
The platform supports two types of users — homeowners and contractors — stored in a
single Users table differentiated by a role attribute.

**Data managed:**
- Full name, email address, phone number
- Street address, city, state, and ZIP code
- Account role (homeowner or contractor)
- Account registration date
- Active/inactive account status

---

### 2.2 Service Categories
A set of predefined service categories used to classify both contractor listings and
homeowner requests.

**Data managed:**
- Category name (e.g. Plumbing, Electrical, Landscaping, HVAC, Cleaning, Painting,
  Carpentry, Roofing)
- Category description

---

### 2.3 Service Listings
Service listings are posted by contractors to advertise what they offer.

**Data managed:**
- Contractor (linked to Users)
- Service category (linked to Service Categories)
- Title and full description of the service
- Minimum and maximum price range
- Service location
- Listing status (active or inactive)
- Date posted

---

### 2.4 Service Requests
Service requests are posted by homeowners when they need a job done.

**Data managed:**
- Homeowner (linked to Users)
- Service category (linked to Service Categories)
- Description of the work needed
- Location of the job
- Request status (open, in review, or closed)
- Date posted

---

### 2.5 Quotes
Quotes are submitted by contractors in response to open service requests.

**Data managed:**
- The service request being quoted (linked to Service Requests)
- The contractor submitting the quote (linked to Users)
- Quoted price amount
- Message to the homeowner
- Quote status (pending, accepted, or rejected)
- Date submitted

---

### 2.6 Bookings
A booking is created when a homeowner accepts a contractor's quote. Each booking
corresponds to exactly one accepted quote.

**Data managed:**
- The accepted quote (linked to Quotes)
- Homeowner and contractor involved (linked to Users)
- Scheduled date and time for the job
- Booking status (scheduled, in progress, completed, or cancelled)
- Date booking was created

---

### 2.7 Reviews
Reviews are written by homeowners after a booking has been completed. Each completed
booking can receive at most one review.

**Data managed:**
- The completed booking (linked to Bookings)
- The reviewer and the contractor being reviewed (linked to Users)
- Star rating (1 to 5)
- Written review comment
- Date review was submitted

---

## 3. Operations and Functions the Application Will Support

The application supports four categories of operations: insert, delete, update, and query.

---

### 3.1 Insert Operations

| Operation | Description |
|---|---|
| Register a new user | Add a homeowner or contractor to the platform |
| Post a service listing | Contractor creates a new service offering |
| Submit a service request | Homeowner posts a new job request |
| Submit a quote | Contractor responds to a request with a price and message |
| Create a booking | System creates a booking when a quote is accepted |
| Submit a review | Homeowner leaves a rating and comment after job completion |
| Add a service category | Admin adds a new category to the platform |

---

### 3.2 Delete Operations

| Operation | Description |
|---|---|
| Remove a service listing | Contractor or admin removes a listing no longer offered |
| Delete a quote | Contractor withdraws a pending quote |
| Cancel and remove a booking | Remove a cancelled booking record |
| Delete a user account | Deactivate and remove a user from the platform |

---

### 3.3 Update Operations

| Operation | Description |
|---|---|
| Update booking status | Progress a booking through scheduled → in progress → completed |
| Update service request status | Mark a request as in review or closed |
| Update quote status | Accept or reject a submitted quote |
| Edit a service listing | Contractor updates title, description, or pricing |
| Update user profile | User edits their contact or location information |

---

### 3.4 Query Operations

| Operation | Description |
|---|---|
| Search contractors by category | Find all active listings in a given service category |
| Search contractors by location | Filter listings by city or region |
| Search by max price | Find listings within a homeowner's budget |
| View all quotes on a request | See all contractor quotes for a given job |
| View booking history | All past and active bookings for a user |
| View reviews for a contractor | All reviews received by a specific contractor |
| Average contractor rating | Computed average star rating per contractor |
| View open service requests | All requests currently in 'open' status |

---

## 4. Integrity Constraints

The following rules are enforced at the database level to maintain data consistency:

| Constraint | Rule |
|---|---|
| Email uniqueness | No two users may share the same email address |
| Category name uniqueness | No duplicate category names |
| Valid price range | A listing's minimum price must not exceed its maximum price |
| Positive quote amount | A quote's amount must be greater than zero |
| Valid rating | A review rating must be between 1 and 5 |
| No self-review | A user cannot review themselves |
| One booking per quote | Each accepted quote can produce at most one booking |
| One review per booking | Each completed booking can receive at most one review |
| Booking requires accepted quote | A booking is only created from a quote with 'accepted' status |
| Referential integrity | All foreign key relationships are enforced with appropriate cascade rules |

---

## 5. Summary

| Item | Detail |
|---|---|
| Application type | Web-based database application |
| Database | MySQL — 7 tables |
| Backend | PHP (prepared statements throughout) |
| Total entities | Users, Service Categories, Service Listings, Service Requests, Quotes, Bookings, Reviews |
| Core operations | Insert, Delete, Update, Query (one web page each, plus full application) |
| Integrity constraints | 10 enforced at database level |
