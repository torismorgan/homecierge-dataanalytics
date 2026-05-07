-- ============================================================
-- HomeCierge Database Schema
-- Platform: MySQL
-- Description: Connects homeowners with contractors for home services
-- ============================================================

-- Drop tables in reverse dependency order for clean rebuilds
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS quotes;
DROP TABLE IF EXISTS service_requests;
DROP TABLE IF EXISTS service_listings;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS users;

-- ============================================================
-- USERS
-- Stores both homeowners and contractors (differentiated by role)
-- ============================================================
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(50)  NOT NULL,
    last_name     VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    phone         VARCHAR(20),
    address       VARCHAR(150),
    city          VARCHAR(80),
    state         VARCHAR(50),
    zip           VARCHAR(10),
    role          ENUM('homeowner', 'contractor') NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active     BOOLEAN DEFAULT TRUE
);

-- ============================================================
-- SERVICE CATEGORIES
-- Predefined categories (e.g. plumbing, electrical, landscaping)
-- ============================================================
CREATE TABLE service_categories (
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(80)  NOT NULL UNIQUE,
    description   VARCHAR(255)
);

-- ============================================================
-- SERVICE LISTINGS
-- Services posted by contractors
-- ============================================================
CREATE TABLE service_listings (
    listing_id    INT AUTO_INCREMENT PRIMARY KEY,
    contractor_id INT NOT NULL,
    category_id   INT NOT NULL,
    title         VARCHAR(150) NOT NULL,
    description   TEXT,
    price_min     DECIMAL(10,2),
    price_max     DECIMAL(10,2),
    location      VARCHAR(150),
    status        ENUM('active', 'inactive') DEFAULT 'active',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_listing_contractor
        FOREIGN KEY (contractor_id) REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_listing_category
        FOREIGN KEY (category_id) REFERENCES service_categories(category_id)
        ON DELETE RESTRICT,

    CONSTRAINT chk_price_range
        CHECK (price_min <= price_max)
);

-- ============================================================
-- SERVICE REQUESTS
-- Requests posted by homeowners looking for a contractor
-- ============================================================
CREATE TABLE service_requests (
    request_id    INT AUTO_INCREMENT PRIMARY KEY,
    homeowner_id  INT NOT NULL,
    category_id   INT NOT NULL,
    description   TEXT NOT NULL,
    location      VARCHAR(150),
    status        ENUM('open', 'in_review', 'closed') DEFAULT 'open',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_request_homeowner
        FOREIGN KEY (homeowner_id) REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_request_category
        FOREIGN KEY (category_id) REFERENCES service_categories(category_id)
        ON DELETE RESTRICT
);

-- ============================================================
-- QUOTES
-- Contractors submit quotes in response to service requests
-- ============================================================
CREATE TABLE quotes (
    quote_id      INT AUTO_INCREMENT PRIMARY KEY,
    request_id    INT NOT NULL,
    contractor_id INT NOT NULL,
    amount        DECIMAL(10,2) NOT NULL,
    message       TEXT,
    status        ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    submitted_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_quote_request
        FOREIGN KEY (request_id) REFERENCES service_requests(request_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_quote_contractor
        FOREIGN KEY (contractor_id) REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT chk_positive_amount
        CHECK (amount > 0)
);

-- ============================================================
-- BOOKINGS
-- Created when a homeowner accepts a quote
-- One booking per accepted quote (enforced by UNIQUE on quote_id)
-- ============================================================
CREATE TABLE bookings (
    booking_id    INT AUTO_INCREMENT PRIMARY KEY,
    quote_id      INT NOT NULL UNIQUE,
    homeowner_id  INT NOT NULL,
    contractor_id INT NOT NULL,
    scheduled_at  DATETIME NOT NULL,
    status        ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_quote
        FOREIGN KEY (quote_id) REFERENCES quotes(quote_id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_booking_homeowner
        FOREIGN KEY (homeowner_id) REFERENCES users(user_id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_booking_contractor
        FOREIGN KEY (contractor_id) REFERENCES users(user_id)
        ON DELETE RESTRICT
);

-- ============================================================
-- REVIEWS
-- Homeowners review contractors after a completed booking
-- One review per booking (enforced by UNIQUE on booking_id)
-- ============================================================
CREATE TABLE reviews (
    review_id     INT AUTO_INCREMENT PRIMARY KEY,
    booking_id    INT NOT NULL UNIQUE,
    reviewer_id   INT NOT NULL,
    reviewee_id   INT NOT NULL,
    rating        TINYINT NOT NULL,
    comment       TEXT,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_review_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_review_reviewer
        FOREIGN KEY (reviewer_id) REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_review_reviewee
        FOREIGN KEY (reviewee_id) REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT chk_rating_range
        CHECK (rating BETWEEN 1 AND 5),

    CONSTRAINT chk_no_self_review
        CHECK (reviewer_id != reviewee_id)
);
