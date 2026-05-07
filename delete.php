<?php
require 'db.php';
$conn = get_db();

$success = '';
$error   = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'])) {
    $listing_id = (int) $_POST['listing_id'];

    // Only allow delete if no accepted/pending bookings are tied to this contractor's listings
    $stmt = $conn->prepare(
        "DELETE FROM service_listings WHERE listing_id = ?"
    );
    $stmt->bind_param("i", $listing_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $success = "Listing removed successfully.";
        } else {
            $error = "Listing not found.";
        }
    } else {
        $error = "Could not delete listing: " . $stmt->error;
    }
    $stmt->close();
}

// Load all listings with contractor name and category
$listings = $conn->query(
    "SELECT sl.listing_id, sl.title, sl.status, sl.price_min, sl.price_max,
            sc.name AS category,
            CONCAT(u.first_name, ' ', u.last_name) AS contractor
     FROM service_listings sl
     JOIN users u ON sl.contractor_id = u.user_id
     JOIN service_categories sc ON sl.category_id = sc.category_id
     ORDER BY sl.created_at DESC"
);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Listings — HomeCierge</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; }
        header { background: #1a1a2e; color: #fff; padding: 18px 32px; }
        header h1 { font-size: 1.4rem; font-weight: 600; }
        header span { color: #e94560; }
        nav { background: #16213e; padding: 10px 32px; display: flex; gap: 20px; }
        nav a { color: #aaa; text-decoration: none; font-size: 0.9rem; }
        nav a:hover, nav a.active { color: #e94560; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        h2 { font-size: 1.3rem; margin-bottom: 6px; }
        p.sub { color: #777; font-size: 0.9rem; margin-bottom: 28px; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert.success { background: #e6f9f0; color: #1a7a4a; border: 1px solid #b2dfca; }
        .alert.error   { background: #fdecea; color: #b71c1c; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
        thead { background: #1a1a2e; color: #fff; }
        thead th { padding: 14px 16px; text-align: left; font-size: 0.85rem; font-weight: 600; }
        tbody td { padding: 14px 16px; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .badge.active   { background: #e6f9f0; color: #1a7a4a; }
        .badge.inactive { background: #f0f0f0; color: #888; }
        .btn-delete {
            background: #e94560; color: #fff; border: none;
            padding: 7px 16px; border-radius: 6px; font-size: 0.85rem;
            cursor: pointer; font-weight: 600;
        }
        .btn-delete:hover { background: #c73652; }
        .empty { text-align: center; padding: 40px; color: #aaa; }
    </style>
</head>
<body>
<header><h1>Home<span>Cierge</span></h1></header>
<nav>
    <a href="insert.php">New Request</a>
    <a href="query.php">Find Contractors</a>
    <a href="update.php">My Bookings</a>
    <a href="delete.php" class="active">Manage Listings</a>
</nav>

<div class="container">
    <h2>Manage Service Listings</h2>
    <p class="sub">Remove listings that are no longer available.</p>

    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Contractor</th>
                <th>Price Range</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($listings->num_rows === 0): ?>
                <tr><td colspan="7" class="empty">No listings found.</td></tr>
            <?php else: ?>
                <?php while ($row = $listings->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['listing_id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= htmlspecialchars($row['contractor']) ?></td>
                    <td>$<?= number_format($row['price_min'], 0) ?> – $<?= number_format($row['price_max'], 0) ?></td>
                    <td><span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this listing? This cannot be undone.');">
                            <input type="hidden" name="listing_id" value="<?= $row['listing_id'] ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
