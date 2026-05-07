<?php
require 'db.php';
$conn = get_db();

$success = '';
$error   = '';

// Load homeowners and categories for dropdowns
$homeowners = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'homeowner' AND is_active = 1");
$categories = $conn->query("SELECT category_id, name FROM service_categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $homeowner_id = (int) $_POST['homeowner_id'];
    $category_id  = (int) $_POST['category_id'];
    $description  = trim($_POST['description']);
    $location     = trim($_POST['location']);

    if ($homeowner_id && $category_id && $description && $location) {
        $stmt = $conn->prepare(
            "INSERT INTO service_requests (homeowner_id, category_id, description, location, status)
             VALUES (?, ?, ?, ?, 'open')"
        );
        $stmt->bind_param("iiss", $homeowner_id, $category_id, $description, $location);

        if ($stmt->execute()) {
            $success = "Service request submitted successfully. Request ID: " . $stmt->insert_id;
        } else {
            $error = "Error submitting request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Service Request — HomeCierge</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; }
        header { background: #1a1a2e; color: #fff; padding: 18px 32px; }
        header h1 { font-size: 1.4rem; font-weight: 600; }
        header span { color: #e94560; }
        nav { background: #16213e; padding: 10px 32px; display: flex; gap: 20px; }
        nav a { color: #aaa; text-decoration: none; font-size: 0.9rem; }
        nav a:hover, nav a.active { color: #e94560; }
        .container { max-width: 680px; margin: 40px auto; padding: 0 20px; }
        h2 { font-size: 1.3rem; margin-bottom: 6px; }
        p.sub { color: #777; font-size: 0.9rem; margin-bottom: 28px; }
        .card { background: #fff; border-radius: 10px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #555; }
        input, select, textarea {
            width: 100%; padding: 10px 14px; border: 1px solid #ddd;
            border-radius: 6px; font-size: 0.95rem; margin-bottom: 20px;
            font-family: inherit; background: #fafafa;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: #e94560; background: #fff;
        }
        textarea { resize: vertical; min-height: 100px; }
        button {
            background: #e94560; color: #fff; border: none;
            padding: 12px 28px; border-radius: 6px; font-size: 1rem;
            cursor: pointer; font-weight: 600; width: 100%;
        }
        button:hover { background: #c73652; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert.success { background: #e6f9f0; color: #1a7a4a; border: 1px solid #b2dfca; }
        .alert.error   { background: #fdecea; color: #b71c1c; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<header><h1>Home<span>Cierge</span></h1></header>
<nav>
    <a href="insert.php" class="active">New Request</a>
    <a href="query.php">Find Contractors</a>
    <a href="update.php">My Bookings</a>
    <a href="delete.php">Manage Listings</a>
</nav>

<div class="container">
    <h2>Submit a Service Request</h2>
    <p class="sub">Tell us what you need — contractors in your area will send quotes.</p>

    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <label for="homeowner_id">Homeowner</label>
            <select name="homeowner_id" id="homeowner_id" required>
                <option value="">-- Select homeowner --</option>
                <?php while ($row = $homeowners->fetch_assoc()): ?>
                    <option value="<?= $row['user_id'] ?>"
                        <?= (isset($_POST['homeowner_id']) && $_POST['homeowner_id'] == $row['user_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="category_id">Service Category</label>
            <select name="category_id" id="category_id" required>
                <option value="">-- Select category --</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['category_id'] ?>"
                        <?= (isset($_POST['category_id']) && $_POST['category_id'] == $row['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="description">Describe the job</label>
            <textarea name="description" id="description" placeholder="E.g. Kitchen sink has been leaking under the cabinet for two days." required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>

            <label for="location">Your Location</label>
            <input type="text" name="location" id="location" placeholder="E.g. Dallas, TX"
                value="<?= isset($_POST['location']) ? htmlspecialchars($_POST['location']) : '' ?>" required>

            <button type="submit">Submit Request</button>
        </form>
    </div>
</div>
</body>
</html>
