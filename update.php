<?php
require 'db.php';
$conn = get_db();

$success = '';
$error   = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = (int) $_POST['booking_id'];
    $new_status = $_POST['status'];
    $allowed    = ['scheduled', 'in_progress', 'completed', 'cancelled'];

    if (in_array($new_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);

        if ($stmt->execute()) {
            $success = "Booking #$booking_id updated to " . ucfirst(str_replace('_', ' ', $new_status)) . ".";
        } else {
            $error = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Invalid status value.";
    }
}

// Load all bookings with related info
$bookings = $conn->query(
    "SELECT b.booking_id, b.scheduled_at, b.status,
            CONCAT(ho.first_name, ' ', ho.last_name) AS homeowner,
            CONCAT(co.first_name, ' ', co.last_name) AS contractor,
            sc.name AS category,
            q.amount
     FROM bookings b
     JOIN users ho ON b.homeowner_id = ho.user_id
     JOIN users co ON b.contractor_id = co.user_id
     JOIN quotes q  ON b.quote_id = q.quote_id
     JOIN service_requests sr ON q.request_id = sr.request_id
     JOIN service_categories sc ON sr.category_id = sc.category_id
     ORDER BY b.scheduled_at DESC"
);

$conn->close();

$status_options = ['scheduled', 'in_progress', 'completed', 'cancelled'];
$status_labels  = [
    'scheduled'   => 'Scheduled',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
    'cancelled'   => 'Cancelled',
];
$status_colors  = [
    'scheduled'   => '#1565c0',
    'in_progress' => '#e65100',
    'completed'   => '#1a7a4a',
    'cancelled'   => '#888',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings — HomeCierge</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; }
        header { background: #1a1a2e; color: #fff; padding: 18px 32px; }
        header h1 { font-size: 1.4rem; font-weight: 600; }
        header span { color: #e94560; }
        nav { background: #16213e; padding: 10px 32px; display: flex; gap: 20px; }
        nav a { color: #aaa; text-decoration: none; font-size: 0.9rem; }
        nav a:hover, nav a.active { color: #e94560; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        h2 { font-size: 1.3rem; margin-bottom: 6px; }
        p.sub { color: #777; font-size: 0.9rem; margin-bottom: 28px; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert.success { background: #e6f9f0; color: #1a7a4a; border: 1px solid #b2dfca; }
        .alert.error   { background: #fdecea; color: #b71c1c; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
        thead { background: #1a1a2e; color: #fff; }
        thead th { padding: 14px 16px; text-align: left; font-size: 0.85rem; font-weight: 600; }
        tbody td { padding: 13px 16px; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; color: #fff; }
        select.status-select {
            padding: 7px 10px; border-radius: 6px; border: 1px solid #ddd;
            font-size: 0.85rem; background: #fafafa; cursor: pointer;
        }
        .btn-update {
            background: #1a1a2e; color: #fff; border: none;
            padding: 7px 16px; border-radius: 6px; font-size: 0.85rem;
            cursor: pointer; font-weight: 600; margin-left: 8px;
        }
        .btn-update:hover { background: #e94560; }
        .empty { text-align: center; padding: 40px; color: #aaa; }
        .inline-form { display: flex; align-items: center; gap: 6px; }
    </style>
</head>
<body>
<header><h1>Home<span>Cierge</span></h1></header>
<nav>
    <a href="insert.php">New Request</a>
    <a href="query.php">Find Contractors</a>
    <a href="update.php" class="active">My Bookings</a>
    <a href="delete.php">Manage Listings</a>
</nav>

<div class="container">
    <h2>Booking Management</h2>
    <p class="sub">Update the status of active bookings as work progresses.</p>

    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Homeowner</th>
                <th>Contractor</th>
                <th>Category</th>
                <th>Scheduled</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bookings->num_rows === 0): ?>
                <tr><td colspan="8" class="empty">No bookings found.</td></tr>
            <?php else: ?>
                <?php while ($row = $bookings->fetch_assoc()):
                    $color = $status_colors[$row['status']] ?? '#888';
                ?>
                <tr>
                    <td><?= $row['booking_id'] ?></td>
                    <td><?= htmlspecialchars($row['homeowner']) ?></td>
                    <td><?= htmlspecialchars($row['contractor']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= date('M j, Y g:i A', strtotime($row['scheduled_at'])) ?></td>
                    <td>$<?= number_format($row['amount'], 2) ?></td>
                    <td>
                        <span class="badge" style="background:<?= $color ?>">
                            <?= $status_labels[$row['status']] ?? ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                            <select name="status" class="status-select">
                                <?php foreach ($status_options as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $opt === $row['status'] ? 'selected' : '' ?>>
                                        <?= $status_labels[$opt] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-update">Save</button>
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
