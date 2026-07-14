<?php
include 'db.php';
$pageTitle = 'Dashboard — Zero Hunger Admin';
include 'header.php';

// Get stats from dashboard_stats view
$stats_query = mysqli_query($conn, "SELECT * FROM dashboard_stats");
$stats = mysqli_fetch_assoc($stats_query);

$total_donations = $stats['total_donations'] ?? 0;
$completed_deliveries = $stats['completed_deliveries'] ?? 0;
$available_food = $stats['available_food'] ?? 0;
$total_users = $stats['total_users'] ?? 0;
$pending_requests = $stats['pending_requests'] ?? 0;
$total_donors = $stats['total_donors'] ?? 0;
$total_ngos = $stats['total_ngos'] ?? 0;
$total_riders = $stats['total_riders'] ?? 0;

// Get monthly data for chart
$monthly_data = mysqli_query($conn, "SELECT * FROM monthly_donations ORDER BY month ASC LIMIT 6");
$months = [];
$donations_count = [];
$completed_count = [];
while($row = mysqli_fetch_assoc($monthly_data)) {
    $months[] = $row['month'];
    $donations_count[] = $row['total_donations'];
    $completed_count[] = $row['completed'];
}

// Get role distribution
$role_data = mysqli_query($conn, "SELECT * FROM role_distribution");
$role_names = [];
$role_counts = [];
while($row = mysqli_fetch_assoc($role_data)) {
    $role_names[] = $row['role_name'];
    $role_counts[] = $row['total_users'];
}

// Calculate percentage for progress
$donation_percentage = $total_donations > 0 ? round(($completed_deliveries / $total_donations) * 100) : 0;
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.stat-card {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-card);
    padding: 1.25rem 1.25rem;
    transition: transform .18s, box-shadow .18s;
    height: 100%;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 24px rgba(0,0,0,.09);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    margin-bottom: 1rem;
}
.stat-icon.green { background: var(--green-100); color: var(--green-600); }
.stat-icon.blue { background: #dbeafe; color: #2563eb; }
.stat-icon.amber { background: #fef3c7; color: #b45309; }
.stat-icon.orange { background: #ffedd5; color: #ea580c; }

.stat-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--gray-400);
    margin-bottom: 5px;
}
.stat-main-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--gray-900);
    line-height: 1;
    margin-bottom: 8px;
}
.stat-suffix {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-400);
    margin-left: 4px;
}
.stat-detail {
    font-size: 11px;
    color: var(--gray-500);
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--gray-100);
}
.stat-detail-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-right: 12px;
}
.stat-detail-item i {
    font-size: 10px;
}
.stat-detail-item .donor { color: var(--green-600); }
.stat-detail-item .ngo { color: #ea580c; }
.stat-detail-item .rider { color: #b45309; }

.stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 8px;
    padding: 3px 10px;
    border-radius: 20px;
}
.stat-trend.green { background: var(--green-50); color: var(--green-600); }
.stat-trend.amber { background: #fffbeb; color: #b45309; }
.stat-trend.orange { background: #fff7ed; color: #ea580c; }
.stat-trend.blue { background: #eff6ff; color: #2563eb; }

/* Chart Cards */
.chart-card {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    height: 100%;
}
.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
}
.chart-header h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: var(--gray-900);
}
.chart-body {
    padding: 1.25rem;
    height: 320px;
    position: relative;
}
.chart-body canvas {
    max-height: 280px;
    width: 100%;
}

/* Progress Card */
.progress-card {
    background: linear-gradient(135deg, var(--green-500), var(--green-600));
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    color: white;
    height: 100%;
}
.progress-title {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 8px;
}
.progress-value {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 10px;
}
.progress-bar-bg {
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
    height: 8px;
    overflow: hidden;
}
.progress-bar-fill {
    background: white;
    height: 100%;
    border-radius: 10px;
    width: 0%;
    transition: width 1s ease;
}
.progress-stats {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    font-size: 11px;
    opacity: 0.8;
}

/* New Card Design Matching Donor Dashboard */
.dh-card {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.dh-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #2e7d32, #1b5e20);
    color: white;
    border-bottom: none;
}
.dh-header-icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    background: rgba(255,255,255,0.2);
    color: white;
}
.dh-card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: white;
}
.dh-card-header p {
    margin: 0;
    font-size: 12.5px;
    color: rgba(255,255,255,0.85);
}
.dh-card-header-right {
    margin-left: auto;
}
.welcome-body {
    padding: 1.75rem 1.5rem;
}
.welcome-body h2 {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--gray-900);
    margin-bottom: 0.6rem;
}
.welcome-body h2 span {
    color: var(--green-500);
}
.welcome-body p {
    font-size: 14px;
    color: var(--gray-500);
    line-height: 1.7;
    max-width: 720px;
    margin: 0 0 1.5rem;
}

.quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.quick-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    border-radius: var(--radius-sm);
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    border: 1.5px solid var(--gray-200);
    background: var(--gray-50);
    color: var(--gray-700);
    transition: all 0.15s;
}
.quick-link:hover {
    border-color: var(--green-400);
    background: var(--green-50);
    color: var(--green-700);
    transform: translateY(-1px);
}

/* Dark mode overrides */
body.dark-mode .stat-card,
body.dark-mode .chart-card,
body.dark-mode .dh-card {
    background: #2a2a3a;
    border-color: #3a3a4a;
}
body.dark-mode .chart-header {
    background: #1e1e2e;
    border-bottom-color: #3a3a4a;
}
body.dark-mode .chart-header h6 {
    color: #fff;
}
body.dark-mode .stat-main-value,
body.dark-mode .stat-detail {
    color: #fff;
}
body.dark-mode .stat-detail {
    border-top-color: #3a3a4a;
}
body.dark-mode .dh-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
body.dark-mode .welcome-body h2 {
    color: #fff;
}
body.dark-mode .welcome-body p {
    color: #aaa;
}
body.dark-mode .quick-link {
    background: #1e1e2e;
    border-color: #3a3a4a;
    color: #ddd;
}
body.dark-mode .quick-link:hover {
    background: #2a2a3a;
    border-color: #3b82f6;
    color: #fff;
}
</style>

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    <!-- Card 1: Available Food -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-hand-holding-heart"></i></div>
            <div>
                <div class="stat-label">Available Food</div>
                <div class="stat-main-value"><?= $available_food ?> <span class="stat-suffix">Items</span></div>
                <div class="stat-trend green"><i class="fas fa-circle" style="font-size:6px;"></i> Live listings</div>
            </div>
        </div>
    </div>
    
    <!-- Card 2: Portal Users -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-label">Portal Users</div>
                <div class="stat-main-value"><?= $total_users ?> <span class="stat-suffix">Total</span></div>
                <div class="stat-detail">
                    <span class="stat-detail-item"><i class="fas fa-circle donor"></i> <?= $total_donors ?> Donors</span>
                    <span class="stat-detail-item"><i class="fas fa-circle ngo"></i> <?= $total_ngos ?> NGOs</span>
                    <span class="stat-detail-item"><i class="fas fa-circle rider"></i> <?= $total_riders ?> Riders</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 3: NGO Requests -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-bullhorn"></i></div>
            <div>
                <div class="stat-label">NGO Requests</div>
                <div class="stat-main-value"><?= $pending_requests ?> <span class="stat-suffix">Pending</span></div>
                <div class="stat-trend amber"><i class="fas fa-circle" style="font-size:6px;"></i> Awaiting action</div>
            </div>
        </div>
    </div>
    
    <!-- Card 4: Dispatches -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-truck"></i></div>
            <div>
                <div class="stat-label">Dispatches</div>
                <div class="stat-main-value"><?= $completed_deliveries ?> <span class="stat-suffix">Delivered</span></div>
                <div class="stat-trend orange"><i class="fas fa-circle" style="font-size:6px;"></i> Completed</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Monthly Donations Chart -->
    <div class="col-md-8">
        <div class="chart-card">
            <div class="chart-header">
                <h6><i class="fas fa-chart-line me-2 text-success"></i> Donations Trend (Last 6 Months)</h6>
                <span class="badge bg-success">Live Data</span>
            </div>
            <div class="chart-body">
                <canvas id="donationsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Role Distribution Chart -->
    <div class="col-md-4">
        <div class="chart-card">
            <div class="chart-header">
                <h6><i class="fas fa-chart-pie me-2 text-primary"></i> User Role Distribution</h6>
                <span class="badge bg-primary">Live Data</span>
            </div>
            <div class="chart-body">
                <canvas id="roleChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Progress & Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="progress-card">
            <div class="progress-title">Overall Delivery Success Rate</div>
            <div class="progress-value"><?= $donation_percentage ?>%</div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?= $donation_percentage ?>%;"></div>
            </div>
            <div class="progress-stats">
                <span><i class="fas fa-check-circle me-1"></i> <?= $completed_deliveries ?> Delivered</span>
                <span><i class="fas fa-clock me-1"></i> <?= $total_donations - $completed_deliveries ?> Pending</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="progress-card" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
            <div class="progress-title">Platform Growth</div>
            <div class="progress-value"><?= $total_users ?> Users</div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: 100%;"></div>
            </div>
            <div class="progress-stats">
                <span><i class="fas fa-hand-holding-heart me-1"></i> <?= $total_donors ?> Donors</span>
                <span><i class="fas fa-building me-1"></i> <?= $total_ngos ?> NGOs</span>
                <span><i class="fas fa-motorcycle me-1"></i> <?= $total_riders ?> Riders</span>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Card - Now styled like donor dashboard cards -->
<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-header-icon"><i class="fas fa-tachometer-alt"></i></div>
        <div>
            <h5>Admin Control Panel</h5>
            <p>Zero Hunger Network — Platform Overview</p>
        </div>
    </div>
    <div class="welcome-body">
        <h2>Welcome to <span>Zero Hunger</span> Admin</h2>
        <p>Apne platform par surplus food donations tracking, NGO allocations, aur volunteer networks ko manage karne ke liye left sidebar ke menus ka istemal karein. Upar diye gaye cards se platform ki real-time activity monitor karein.</p>
        <div class="quick-links">
            <a href="food_donations.php" class="quick-link"><i class="fas fa-hand-holding-heart"></i> Donations</a>
            <a href="users.php" class="quick-link"><i class="fas fa-users"></i> Users</a>
            <a href="requests.php" class="quick-link"><i class="fas fa-clipboard-list"></i> NGO Requests</a>
            <a href="manage_riders.php" class="quick-link"><i class="fas fa-motorcycle"></i> Riders</a>
            <a href="food_deliveries.php" class="quick-link"><i class="fas fa-truck"></i> Deliveries</a>
            <a href="view_all_deliveries.php" class="quick-link"><i class="fas fa-map-marked-alt"></i> Live Tracking</a>
            <a href="feedback.php" class="quick-link"><i class="fas fa-comment-dots"></i> Feedback</a>
            <a href="reports.php" class="quick-link"><i class="fas fa-chart-bar"></i> Reports</a>
        </div>
    </div>
</div>

<script>
// Donations Trend Chart (Line Chart)
const ctx1 = document.getElementById('donationsChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [
            {
                label: 'Total Donations',
                data: <?= json_encode($donations_count) ?>,
                borderColor: '#2e9458',
                backgroundColor: 'rgba(46,148,88,0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2e9458',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7
            },
            {
                label: 'Completed Deliveries',
                data: <?= json_encode($completed_count) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
                labels: { font: { size: 12 }, usePointStyle: true, boxWidth: 8 }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw + ' items';
                    }
                }
            }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});

// Role Distribution Chart (Pie Chart)
const ctx2 = document.getElementById('roleChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($role_names) ?>,
        datasets: [{
            data: <?= json_encode($role_counts) ?>,
            backgroundColor: ['#2e9458', '#ea580c', '#b45309'],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 11 }, usePointStyle: true, boxWidth: 10, padding: 15 }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                        return `${label}: ${value} users (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '60%'
    }
});
</script>

<?php include 'footer.php'; ?>