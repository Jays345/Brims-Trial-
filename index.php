<?php include 'db.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.html");
  exit;
}
?>


<?php
// Fetch key stats
$totalProducts = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$totalSuppliers = $conn->query("SELECT COUNT(*) AS count FROM suppliers")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status='open'")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) AS total FROM sales")->fetch_assoc()['total'] ?? 0;

// Low stock items
$lowStock = $conn->query("SELECT product_name, sku, stock, category FROM products WHERE stock < 10 LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | BRIMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="dashboard.css">
  
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div>
      <div class="logo">
        <img src="https://cdn-icons-png.flaticon.com/512/1828/1828673.png" alt="logo">
        <h2>BRIMS</h2>
      </div>
      <nav>
        <ul>
          <li><a href="#" class="active">Dashboard</a></li>
          <li><a href="#">Users</a></li>
          <li><a href="#">Products</a></li>
          <li><a href="#">Orders</a></li>
          <li><a href="#">Suppliers</a></li>
          <li><a href="#">Reports</a></li>
        </ul>
      </nav>
    </div>
    <footer>© 2025 BRIMS</footer>
  </div>

  <!-- Main -->
  <div class="main-content">
    <header>
      <h1>Admin Dashboard</h1>
      <div class="user-info">
        <span>Welcome, Admin</span>
        <img src="https://i.pravatar.cc/40" alt="User">
      </div>
      <a href="logout.php" class="logout-btn">Logout</a>

    </header>

    <!-- Stats -->
    <div class="cards">
      <div class="card">
        <h3>Total Products</h3>
        <p><?= $totalProducts ?></p>
      </div>
      <div class="card">
        <h3>Total Suppliers</h3>
        <p><?= $totalSuppliers ?></p>
      </div>
      <div class="card">
        <h3>Open Orders</h3>
        <p><?= $totalOrders ?></p>
      </div>
      <div class="card">
        <h3>Total Revenue</h3>
        <p>$<?= number_format($totalRevenue, 2) ?></p>
      </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
      <h2>Monthly Profit Overview</h2>
      <canvas id="profitChart"></canvas>
    </div>

    <!-- Low Stock -->
    <h2>Low Stock Alerts</h2>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>SKU</th>
          <th>Stock</th>
          <th>Category</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $lowStock->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= htmlspecialchars($row['sku']) ?></td>
            <td><?= $row['stock'] ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

<script>
setInterval(() => {
  fetch('fetch_data.php')
    .then(res => res.json())
    .then(data => {
      document.querySelector('.cards .card:nth-child(1) p').textContent = data.totalProducts;
      document.querySelector('.cards .card:nth-child(2) p').textContent = data.totalSuppliers;
      document.querySelector('.cards .card:nth-child(3) p').textContent = data.totalOrders;
      document.querySelector('.cards .card:nth-child(4) p').textContent = "$" + data.totalRevenue.toLocaleString();
    });
}, 10000); // refresh every 10 seconds
</script>


  <!-- Chart Script -->
  <script>
    const ctx = document.getElementById('profitChart');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [{
          label: 'Revenue',
          data: [12000, 15000, 11000, 18000, 22000, 19000, 25000],
          borderColor: '#b388ff',
          fill: false,
          tension: 0.4
        }]
      },
      options: {
        plugins: {
          legend: {
            labels: { color: '#fff' }
          }
        },
        scales: {
          x: { ticks: { color: '#bbb' } },
          y: { ticks: { color: '#bbb' } }
        }
      }
    });
  </script>
</body>
</html>
