<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kedai Ibu | Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    body {
      background-color: #f4f6f9;
    }
    .sidebar {
      height: 100vh;
      background-color: #2d3e50;
      color: white;
    }
    .sidebar .nav-link {
      color: #cfd8dc;
    }
    .sidebar .nav-link.active {
      background-color: #1a252f;
      color: #fff;
    }
    .header {
      background-color: #e74c3c;
      padding: 10px 20px;
      color: white;
    }
    .card-summary {
      border-radius: 10px;
      padding: 20px;
    }
    .card-summary.green {
      background-color: #2ecc71;
      color: white;
    }
    .card-summary.purple {
      background-color: #8e44ad;
      color: white;
    }
  </style>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3">
      <h4 class="mb-4 fs-5"><i class="fa fa-shop"></i> Kedai Ibu</h4>   
      <nav class="nav flex-column">
        <a class="nav-link active mb-2" href="../views/dashboard.php"><i class="fa fa-home"></i> Home</a>
        <a class="nav-link mb-2" href="#"><i class="fa fa-users"></i> Employee</a>
        <a class="nav-link mb-2" href="#"><i class="fa fa-box"></i> Product Library</a>
        <a class="nav-link mb-2" href="#"><i class="fa fa-chart-line"></i> Sales Reports</a>
        <a class="nav-link mb-2" href="../views/order.php"><i class="fa fa-shopping-cart"></i> Order</a>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-fill">
      <div class="header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Dashboard</h4>
        <div><i class="fa fa-user"></i> Ahmad Zulkifli</div>
      </div>

      <div class="container mt-4">
        <div class="row mb-3">
          <div class="col-md-3">
            <label for="start">Start Time</label>
            <input type="time" class="form-control" id="start" />
          </div>
          <div class="col-md-3">
            <label for="end">End Time</label>
            <input type="time" class="form-control" id="end" />
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Update</button>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card-summary green">
              <h5>Total Sales Revenue</h5>
              <h2>0.00</h2>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card-summary purple">
              <h5>Total Customers</h5>
              <h2>0</h2>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <canvas id="revenueChart" height="100"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Kont'],
        datasets: [{
          label: 'Revenue',
          data: [287.35, 260.00, 250.00, 0, 0],
          borderColor: '#3498db',
          tension: 0.3,
          fill: false
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        }
      }
    });
  </script>
</body>
</html>
