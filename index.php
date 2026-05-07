<?php require 'db.php'; $conn = get_db();

// Summary stats for dashboard
$total_users       = $conn->query("SELECT COUNT(*) AS n FROM users")->fetch_assoc()['n'];
$total_requests    = $conn->query("SELECT COUNT(*) AS n FROM service_requests")->fetch_assoc()['n'];
$open_requests     = $conn->query("SELECT COUNT(*) AS n FROM service_requests WHERE status = 'open'")->fetch_assoc()['n'];
$total_bookings    = $conn->query("SELECT COUNT(*) AS n FROM bookings")->fetch_assoc()['n'];
$active_bookings   = $conn->query("SELECT COUNT(*) AS n FROM bookings WHERE status IN ('scheduled','in_progress')")->fetch_assoc()['n'];
$total_listings    = $conn->query("SELECT COUNT(*) AS n FROM service_listings WHERE status = 'active'")->fetch_assoc()['n'];

// Recent service requests
$recent_requests = $conn->query(
    "SELECT sr.request_id, sr.description, sr.location, sr.status, sr.created_at,
            sc.name AS category,
            CONCAT(u.first_name, ' ', u.last_name) AS homeowner
     FROM service_requests sr
     JOIN users u ON sr.homeowner_id = u.user_id
     JOIN service_categories sc ON sr.category_id = sc.category_id
     ORDER BY sr.created_at DESC
     LIMIT 5"
);

$conn->close();

$status_colors = [
    'open'        => '#1565c0',
    'in_review'   => '#e65100',
    'closed'      => '#888',
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
    <title>HomeCierge — Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; }

        header {
            background: #1a1a2e; color: #fff;
            padding: 20px 40px; display: flex; align-items: center; justify-content: space-between;
        }
        header h1 { font-size: 1.5rem; font-weight: 700; letter-spacing: 0.5px; }
        header span { color: #e94560; }
        header p { font-size: 0.85rem; color: #aaa; margin-top: 2px; }

        nav { background: #16213e; padding: 10px 40px; display: flex; gap: 24px; }
        nav a { color: #aaa; text-decoration: none; font-size: 0.9rem; padding: 4px 0; }
        nav a:hover, nav a.active { color: #e94560; border-bottom: 2px solid #e94560; }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 24px; }

        .welcome { margin-bottom: 32px; }
        .welcome h2 { font-size: 1.4rem; margin-bottom: 6px; }
        .welcome p { color: #777; font-size: 0.95rem; }

        /* Stats grid */
        .stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; margin-bottom: 40px; }
        .stat-card {
            background: #fff; border-radius: 10px; padding: 22px 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center;
            border-top: 4px solid #e94560;
        }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #1a1a2e; }
        .stat-card .label  { font-size: 0.8rem; color: #888; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Quick links */
        .section-title { font-size: 1rem; font-weight: 700; margin-bottom: 16px; color: #1a1a2e; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-bottom: 40px; }
        .quick-card {
            background: #fff; border-radius: 10px; padding: 24px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-decoration: none; color: #333;
            transition: transform 0.15s, box-shadow 0.15s;
            display: flex; align-items: flex-start; gap: 14px;
        }
        .quick-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.1); }
        .quick-card .icon {
            width: 44px; height: 44px; border-radius: 10px;
            background: #fdeef1; display: flex; align-items: center;
            justify-content: center; font-size: 1.3rem; flex-shrink: 0;
        }
        .quick-card h3 { font-size: 0.95rem; margin-bottom: 4px; }
        .quick-card p  { font-size: 0.82rem; color: #888; line-height: 1.4; }

        /* Recent requests table */
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        thead { background: #1a1a2e; color: #fff; }
        thead th { padding: 13px 16px; text-align: left; font-size: 0.82rem; font-weight: 600; }
        tbody td { padding: 13px 16px; font-size: 0.88rem; border-bottom: 1px solid #f0f0f0; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafafa; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; color: #fff; }
        .desc-cell { max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        footer { text-align: center; padding: 32px; color: #bbb; font-size: 0.82rem; margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <div>
        <h1>Home<span>Cierge</span></h1>
        <p>Connecting homeowners with trusted contractors in Dallas, TX</p>
    </div>
</header>

<nav>
    <a href="index.php" class="active">Dashboard</a>
    <a href="insert.php">New Request</a>
    <a href="query.php">Find Contractors</a>
    <a href="update.php">Bookings</a>
    <a href="delete.php">Manage Listings</a>
</nav>

<div class="container">

    <div class="welcome">
        <h2>Dashboard</h2>
        <p>Overview of all platform activity.</p>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <div class="number"><?= $total_users ?></div>
            <div class="label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $total_listings ?></div>
            <div class="label">Active Listings</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $total_requests ?></div>
            <div class="label">Total Requests</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $open_requests ?></div>
            <div class="label">Open Requests</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $total_bookings ?></div>
            <div class="label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $active_bookings ?></div>
            <div class="label">Active Bookings</div>
        </div>
    </div>

    <!-- Quick links -->
    <p class="section-title">Quick Actions</p>
    <div class="quick-links">
        <a href="insert.php" class="quick-card">
            <div class="icon">+</div>
            <div>
                <h3>Submit a Request</h3>
                <p>Post a new service request and receive quotes from contractors.</p>
            </div>
        </a>
        <a href="query.php" class="quick-card">
            <div class="icon">&#128269;</div>
            <div>
                <h3>Find Contractors</h3>
                <p>Search available contractors by category, location, or budget.</p>
            </div>
        </a>
        <a href="update.php" class="quick-card">
            <div class="icon">&#128197;</div>
            <div>
                <h3>Manage Bookings</h3>
                <p>View and update the status of all active bookings.</p>
            </div>
        </a>
        <a href="delete.php" class="quick-card">
            <div class="icon">&#128221;</div>
            <div>
                <h3>Manage Listings</h3>
                <p>View and remove contractor service listings.</p>
            </div>
        </a>
    </div>

    <!-- Recent requests -->
    <p class="section-title">Recent Service Requests</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Homeowner</th>
                <th>Category</th>
                <th>Description</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $recent_requests->fetch_assoc()):
                $color = $status_colors[$row['status']] ?? '#888';
            ?>
            <tr>
                <td><?= $row['request_id'] ?></td>
                <td><?= htmlspecialchars($row['homeowner']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td class="desc-cell"><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><span class="badge" style="background:<?= $color ?>"><?= ucfirst(str_replace('_',' ',$row['status'])) ?></span></td>
                <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<footer>HomeCierge &copy; <?= date('Y') ?> &mdash; Dallas, TX</footer>
</body>
</html>
