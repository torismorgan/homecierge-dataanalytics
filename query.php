<?php
require 'db.php';
$conn = get_db();

// Load categories for filter dropdown
$categories = $conn->query("SELECT category_id, name FROM service_categories ORDER BY name");

$results     = null;
$result_count = 0;

// Run query only when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $category_id = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
    $location    = isset($_GET['location']) ? trim($_GET['location']) : '';
    $max_price   = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : null;

    $sql = "SELECT sl.listing_id, sl.title, sl.description,
                   sl.price_min, sl.price_max, sl.location,
                   sc.name AS category,
                   CONCAT(u.first_name, ' ', u.last_name) AS contractor,
                   u.phone,
                   IFNULL(ROUND(AVG(r.rating), 1), 'No reviews') AS avg_rating,
                   COUNT(r.review_id) AS review_count
            FROM service_listings sl
            JOIN users u ON sl.contractor_id = u.user_id
            JOIN service_categories sc ON sl.category_id = sc.category_id
            LEFT JOIN bookings b ON b.contractor_id = u.user_id AND b.status = 'completed'
            LEFT JOIN reviews r ON r.booking_id = b.booking_id
            WHERE sl.status = 'active'";

    $params = [];
    $types  = '';

    if ($category_id) {
        $sql     .= " AND sl.category_id = ?";
        $types   .= 'i';
        $params[] = $category_id;
    }

    if ($location !== '') {
        $like     = '%' . $location . '%';
        $sql     .= " AND sl.location LIKE ?";
        $types   .= 's';
        $params[] = $like;
    }

    if ($max_price !== null) {
        $sql     .= " AND sl.price_min <= ?";
        $types   .= 'd';
        $params[] = $max_price;
    }

    $sql .= " GROUP BY sl.listing_id ORDER BY avg_rating DESC, sl.price_min ASC";

    $stmt = $conn->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results      = $stmt->get_result();
    $result_count = $results->num_rows;
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Contractors — HomeCierge</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; }
        header { background: #1a1a2e; color: #fff; padding: 18px 32px; }
        header h1 { font-size: 1.4rem; font-weight: 600; }
        header span { color: #e94560; }
        nav { background: #16213e; padding: 10px 32px; display: flex; gap: 20px; }
        nav a { color: #aaa; text-decoration: none; font-size: 0.9rem; }
        nav a:hover, nav a.active { color: #e94560; }
        .container { max-width: 960px; margin: 40px auto; padding: 0 20px; }
        h2 { font-size: 1.3rem; margin-bottom: 6px; }
        p.sub { color: #777; font-size: 0.9rem; margin-bottom: 28px; }
        .filter-card {
            background: #fff; border-radius: 10px; padding: 24px 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07); margin-bottom: 32px;
        }
        .filter-row { display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 160px; }
        label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 6px; color: #555; }
        input, select {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 6px; font-size: 0.9rem; background: #fafafa;
        }
        input:focus, select:focus { outline: none; border-color: #e94560; background: #fff; }
        .btn-search {
            background: #e94560; color: #fff; border: none;
            padding: 10px 28px; border-radius: 6px; font-size: 0.95rem;
            cursor: pointer; font-weight: 600; white-space: nowrap;
        }
        .btn-search:hover { background: #c73652; }
        .result-header { font-size: 0.9rem; color: #777; margin-bottom: 16px; }
        .result-header strong { color: #333; }
        .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .listing-card {
            background: #fff; border-radius: 10px; padding: 22px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            border-top: 4px solid #e94560;
        }
        .listing-card h3 { font-size: 1rem; margin-bottom: 4px; }
        .listing-card .category { font-size: 0.78rem; color: #e94560; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
        .listing-card .desc { font-size: 0.87rem; color: #555; margin-bottom: 14px; line-height: 1.5; }
        .listing-card .meta { font-size: 0.83rem; color: #666; margin-bottom: 4px; }
        .listing-card .meta span { font-weight: 600; color: #333; }
        .stars { color: #f5a623; font-size: 0.85rem; }
        .no-results { text-align: center; padding: 50px 20px; color: #aaa; background: #fff; border-radius: 10px; }
    </style>
</head>
<body>
<header><h1>Home<span>Cierge</span></h1></header>
<nav>
    <a href="insert.php">New Request</a>
    <a href="query.php" class="active">Find Contractors</a>
    <a href="update.php">My Bookings</a>
    <a href="delete.php">Manage Listings</a>
</nav>

<div class="container">
    <h2>Find Contractors</h2>
    <p class="sub">Search available contractors by service type, location, or budget.</p>

    <div class="filter-card">
        <form method="GET">
            <input type="hidden" name="search" value="1">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="category_id">Service Category</label>
                    <select name="category_id" id="category_id">
                        <option value="">All Categories</option>
                        <?php while ($row = $categories->fetch_assoc()): ?>
                            <option value="<?= $row['category_id'] ?>"
                                <?= (isset($_GET['category_id']) && $_GET['category_id'] == $row['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location"
                        placeholder="E.g. Dallas"
                        value="<?= isset($_GET['location']) ? htmlspecialchars($_GET['location']) : '' ?>">
                </div>

                <div class="filter-group">
                    <label for="max_price">Max Starting Price ($)</label>
                    <input type="number" name="max_price" id="max_price" min="0" step="10"
                        placeholder="E.g. 200"
                        value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                </div>

                <button type="submit" class="btn-search">Search</button>
            </div>
        </form>
    </div>

    <?php if ($results !== null): ?>
        <p class="result-header">
            Showing <strong><?= $result_count ?></strong> result<?= $result_count !== 1 ? 's' : '' ?>
        </p>

        <?php if ($result_count === 0): ?>
            <div class="no-results">No listings match your search. Try adjusting your filters.</div>
        <?php else: ?>
            <div class="cards">
                <?php while ($row = $results->fetch_assoc()):
                    $rating = is_numeric($row['avg_rating'])
                        ? str_repeat('★', round($row['avg_rating'])) . str_repeat('☆', 5 - round($row['avg_rating']))
                        : null;
                ?>
                <div class="listing-card">
                    <div class="category"><?= htmlspecialchars($row['category']) ?></div>
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p class="desc"><?= htmlspecialchars($row['description']) ?></p>
                    <p class="meta">Contractor: <span><?= htmlspecialchars($row['contractor']) ?></span></p>
                    <p class="meta">Phone: <span><?= htmlspecialchars($row['phone']) ?></span></p>
                    <p class="meta">Location: <span><?= htmlspecialchars($row['location']) ?></span></p>
                    <p class="meta">Price: <span>$<?= number_format($row['price_min'], 0) ?> – $<?= number_format($row['price_max'], 0) ?></span></p>
                    <p class="meta">
                        Rating:
                        <?php if ($rating): ?>
                            <span class="stars"><?= $rating ?></span>
                            <span>(<?= $row['review_count'] ?> review<?= $row['review_count'] != 1 ? 's' : '' ?>)</span>
                        <?php else: ?>
                            <span>No reviews yet</span>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
