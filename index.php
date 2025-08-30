<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU Scanner System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .hero-section {
            padding: 100px 0;
            text-align: center;
            color: white;
        }
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
        }
        .dashboard-card:hover {
            transform: translateY(-10px);
            color: inherit;
        }
        .dashboard-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section">
            <h1 class="display-4 mb-4">CTU Scanner System</h1>
            <p class="lead">QR Code Access Control System</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <a href="dashboards/scanner/" class="dashboard-card d-block text-center">
                    <i class="fas fa-qrcode dashboard-icon text-primary"></i>
                    <h3>Scanner Dashboard</h3>
                    <p class="text-muted">Scan QR codes for entry/exit</p>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="dashboards/security/login.php" class="dashboard-card d-block text-center">
                    <i class="fas fa-shield-alt dashboard-icon text-success"></i>
                    <h3>Security Dashboard</h3>
                    <p class="text-muted">Real-time monitoring</p>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="dashboards/admin/login.php" class="dashboard-card d-block text-center">
                    <i class="fas fa-cogs dashboard-icon text-warning"></i>
                    <h3>Admin Dashboard</h3>
                    <p class="text-muted">Analytics & Management</p>
                </a>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6 mx-auto">
                <a href="qr_generator.php?generate=1" class="dashboard-card d-block text-center">
                    <i class="fas fa-print dashboard-icon text-info"></i>
                    <h3>QR Code Generator</h3>
                    <p class="text-muted">Generate QR codes for printing</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>