-- ============================================================
-- HomeCierge Seed Data
-- Sample data for testing all CRUD operations
-- ============================================================

-- ============================================================
-- SERVICE CATEGORIES
-- ============================================================
INSERT INTO service_categories (name, description) VALUES
('Plumbing',        'Pipe repairs, installations, and leak fixes'),
('Electrical',      'Wiring, panel upgrades, and outlet installation'),
('Landscaping',     'Lawn care, tree trimming, and garden design'),
('HVAC',            'Heating, ventilation, and air conditioning'),
('Cleaning',        'Residential deep cleaning and move-out services'),
('Painting',        'Interior and exterior painting services'),
('Carpentry',       'Custom woodwork, decks, and furniture repair'),
('Roofing',         'Roof repairs, inspections, and replacements');

-- ============================================================
-- USERS (4 homeowners, 4 contractors)
-- ============================================================
INSERT INTO users (first_name, last_name, email, phone, address, city, state, zip, role) VALUES
-- Homeowners
('James',   'Carter',   'jcarter@email.com',   '214-555-0101', '112 Oak Ave',      'Dallas',      'TX', '75201', 'homeowner'),
('Priya',   'Sharma',   'psharma@email.com',   '214-555-0102', '340 Maple St',     'Plano',       'TX', '75024', 'homeowner'),
('Marcus',  'Lee',      'mlee@email.com',       '214-555-0103', '78 Birch Blvd',    'Frisco',      'TX', '75034', 'homeowner'),
('Sofia',   'Reyes',    'sreyes@email.com',     '214-555-0104', '901 Elm Dr',       'Irving',      'TX', '75038', 'homeowner'),
-- Contractors
('Derek',   'Flynn',    'dflynn@email.com',     '214-555-0201', '55 Commerce Rd',   'Dallas',      'TX', '75202', 'contractor'),
('Aisha',   'Brown',    'abrown@email.com',     '214-555-0202', '200 Industry Ln',  'Garland',     'TX', '75040', 'contractor'),
('Tom',     'Nguyen',   'tnguyen@email.com',    '214-555-0203', '312 Workshop Way', 'Mesquite',    'TX', '75149', 'contractor'),
('Linda',   'Osei',     'losei@email.com',      '214-555-0204', '88 Trade Blvd',    'Richardson',  'TX', '75080', 'contractor');

-- ============================================================
-- SERVICE LISTINGS (contractors posting their services)
-- ============================================================
INSERT INTO service_listings (contractor_id, category_id, title, description, price_min, price_max, location, status) VALUES
(5, 1, 'Emergency Plumbing Repairs',     'Fast response for leaks, burst pipes, and drain clogs.',          80.00,  250.00, 'Dallas, TX',     'active'),
(5, 2, 'Electrical Panel Upgrades',      'Safe and code-compliant panel replacements and upgrades.',        300.00, 900.00, 'Dallas, TX',     'active'),
(6, 3, 'Full Lawn Care Package',         'Weekly mowing, edging, and seasonal cleanup.',                    60.00,  150.00, 'Garland, TX',    'active'),
(6, 5, 'Move-Out Deep Cleaning',         'Thorough cleaning for apartments and homes before move-out.',     120.00, 300.00, 'Garland, TX',    'active'),
(7, 4, 'AC Installation & Maintenance',  'Central AC installs, tune-ups, and refrigerant recharges.',       200.00, 800.00, 'Mesquite, TX',   'active'),
(7, 6, 'Interior & Exterior Painting',   'Professional painting with prep, prime, and two finish coats.',   350.00, 1200.00,'Mesquite, TX',   'active'),
(8, 7, 'Custom Deck Building',           'Design and build custom wood or composite decks.',                500.00, 3000.00,'Richardson, TX', 'active'),
(8, 8, 'Roof Inspection & Repair',       'Full roof inspections, patching, and shingle replacement.',       150.00, 600.00, 'Richardson, TX', 'active');

-- ============================================================
-- SERVICE REQUESTS (homeowners posting needs)
-- ============================================================
INSERT INTO service_requests (homeowner_id, category_id, description, location, status) VALUES
(1, 1, 'Kitchen sink has been leaking under the cabinet for two days.',         'Dallas, TX',  'open'),
(2, 3, 'Need lawn mowed and edges trimmed before the weekend.',                  'Plano, TX',   'open'),
(3, 4, 'AC unit stopped cooling — need a technician ASAP.',                     'Frisco, TX',  'in_review'),
(4, 5, 'Moving out at end of month, need full apartment cleaned.',               'Irving, TX',  'in_review'),
(1, 6, 'Want to repaint the living room and hallway, about 600 sq ft total.',   'Dallas, TX',  'open'),
(2, 8, 'Missing shingles after last storm, need inspection and patch.',          'Plano, TX',   'closed');

-- ============================================================
-- QUOTES (contractors responding to requests)
-- ============================================================
INSERT INTO quotes (request_id, contractor_id, amount, message, status) VALUES
(1, 5, 120.00, 'I can come out tomorrow morning and fix the leak same day.',              'accepted'),
(1, 7, 145.00, 'Available this week. Will bring all parts needed.',                       'rejected'),
(2, 6, 75.00,  'I can do a full cut and edge Friday afternoon.',                          'accepted'),
(3, 7, 350.00, 'Sounds like a refrigerant issue. I can diagnose and fix Thursday.',       'accepted'),
(4, 6, 180.00, 'Full move-out clean including oven, fridge, and bathrooms.',              'accepted'),
(5, 7, 600.00, 'Two coats on walls and trim included. Can start next week.',              'pending'),
(5, 8, 550.00, 'Competitive price, high quality finish. References available.',           'pending'),
(6, 8, 200.00, 'Inspected similar damage last month nearby. Can patch within 2 days.',   'accepted');

-- ============================================================
-- BOOKINGS (created from accepted quotes)
-- ============================================================
INSERT INTO bookings (quote_id, homeowner_id, contractor_id, scheduled_at, status) VALUES
(1, 1, 5, '2026-05-08 09:00:00', 'scheduled'),
(3, 2, 6, '2026-05-09 14:00:00', 'scheduled'),
(4, 3, 7, '2026-05-08 11:00:00', 'in_progress'),
(5, 4, 6, '2026-05-10 10:00:00', 'scheduled'),
(8, 2, 8, '2026-05-06 08:00:00', 'completed');

-- ============================================================
-- REVIEWS (only for completed bookings)
-- ============================================================
INSERT INTO reviews (booking_id, reviewer_id, reviewee_id, rating, comment) VALUES
(5, 2, 8, 5, 'Linda did an amazing job. Fast, clean, and professional. Highly recommend.');
