<?php
session_start();
require_once 'config.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    
    $fetch = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $fetch->bind_param("i", $id);
    $fetch->execute();
    $user = $fetch->get_result()->fetch_assoc();

    if ($user) {
        $ban = $conn->prepare("INSERT IGNORE INTO banned_users (email, name) VALUES (?, ?)");
        $ban->bind_param("ss", $user['email'], $user['name']);
        $ban->execute();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin_dashboard.php");
    exit();
}
if (isset($_GET['unban'])) {
    $id = intval($_GET['unban']);
    $stmt = $conn->prepare("DELETE FROM banned_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit();
}

$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MarketSA Admin Dashboard</title>

  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background-color: #f9f9f9;
    }

    h1 {
      text-align: center;
      color: #333;
    }

    .top-bar {
      text-align: center;
      margin-bottom: 20px;
    }

    input[type="text"] {
      padding: 10px;
      width: 250px;
    }

    button {
      padding: 10px 15px;
      cursor: pointer;
      border: none;
      background: #007BFF;
      color: white;
      border-radius: 5px;
    }

    table {
      width: 90%;
      margin: auto;
      border-collapse: collapse;
      background: white;
    }

    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }

    th {
      background: #333;
      color: white;
    }

    .delete-btn {
      background: red;
      color: white;
      padding: 6px 10px;
      text-decoration: none;
      border-radius: 4px;
    }

    .delete-btn:hover {
      background: darkred;
    }

    .logout {
      text-align: center;
      margin-top: 20px;
    }

    .logout a {
      text-decoration: none;
      color: white;
      background: black;
      padding: 10px 15px;
      border-radius: 5px;
    }
  </style>
</head>

<body>

<h1>MarketSA Admin Dashboard</h1>

<!-- SEARCH -->
<div class="top-bar">
  <form method="GET">
    <input type="text" name="search" placeholder="Search name or email" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
  </form>
</div>


<table>
  <tr>
    <th>ID</th>
    <th>Name / Store</th>
    <th>Email</th>
    <th>Role</th>
    <th>Action</th>
  </tr>

  <?php if ($result->num_rows > 0) { ?>
      <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
              <td><?php echo $row['id']; ?></td>
              <td><?php echo $row['name']; ?></td>
              <td><?php echo $row['email']; ?></td>
              <td><?php echo $row['role']; ?></td>
              <td>
                  <a class="delete-btn"
                     href="admin_dashboard.php?delete=<?php echo $row['id']; ?>"
                     onclick="return confirm('Delete this user?');">
                     Delete
                  </a>
              </td>
          </tr>
      <?php } ?>
  <?php } else { ?>
      <tr>
          <td colspan="5" style="text-align:center;">No users found</td>
      </tr>
  <?php } ?>

</table>
<?php
$banned = $conn->query("SELECT * FROM banned_users ORDER BY banned_at DESC");
?>

<h2 style="text-align:center;margin-top:40px;color:#333;">Banned Users</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Reason</th>
        <th>Banned At</th>
        <th>Action</th>
    </tr>
    <?php if ($banned->num_rows > 0): ?>
        <?php while ($row = $banned->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><?= $row['banned_at'] ?></td>
            <td>
                <a class="delete-btn" style="background:green;"
                   href="admin_dashboard.php?unban=<?= $row['id'] ?>"
                   onclick="return confirm('Unban this user?');">
                   Unban
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">No banned users</td></tr>
    <?php endif; ?>
</table>


<div class="logout">
  <br>
  <a href="logout.php">Logout</a>
</div>

</body>
</html>