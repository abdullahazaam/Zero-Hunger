<?php
include '../Backend/db.php';
include 'header.php'; // First - defines __() function

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pageTitle = __('invoice') . ' #' . str_pad($request_id, 6, '0', STR_PAD_LEFT);

$query = mysqli_query($conn, "
    SELECT r.*, fd.food_item, fd.quantity, fd.price, 
           u_donor.full_name as donor_name, u_donor.email as donor_email, u_donor.phone as donor_phone,
           u_ngo.full_name as ngo_name, u_ngo.email as ngo_email, u_ngo.phone as ngo_phone,
           u_rider.full_name as rider_name, u_rider.email as rider_email, u_rider.phone as rider_phone,
           u_rider.bike_number
    FROM requests r
    JOIN food_donations fd ON r.donation_id = fd.donation_id
    JOIN users u_donor ON fd.donor_id = u_donor.user_id
    JOIN users u_ngo ON r.receiver_id = u_ngo.user_id
    LEFT JOIN users u_rider ON r.rider_id = u_rider.user_id
    WHERE r.request_id = $request_id
");

$invoice = mysqli_fetch_assoc($query);

if (!$invoice) {
    die(__('invoice_not_found'));
}
?>

<style>
    :root {
        --primary: #2e9458;
        --primary-dark: #226e42;
        --gray-50: #f8f9fc;
        --gray-100: #f1f3f9;
        --gray-200: #e4e7ed;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --radius-lg: 16px;
        --radius-md: 12px;
    }

    body {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        min-height: 100vh;
    }

    /* Dark Mode Body */
    body.dark-mode {
        background: #0a0a0f;
    }

    .invoice-wrapper {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .invoice-card {
        background: #fff;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        overflow: hidden;
    }

    /* Dark Mode Card */
    body.dark-mode .invoice-card {
        background: #1e1e2e;
        border: 1px solid #3a3a4a;
    }

    /* Invoice Header */
    .invoice-header {
        background: linear-gradient(135deg, #1e5631, #2e7d32, #388e3c);
        padding: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    /* Dark Mode Header */
    body.dark-mode .invoice-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb, #3b82f6);
    }

    .invoice-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }

    .invoice-header h1 {
        font-size: 1.8rem;
        font-weight: 800;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: white;
    }

    .invoice-header p {
        margin: 5px 0 0;
        opacity: 0.85;
        font-size: 0.85rem;
        color: white;
    }

    .invoice-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(5px);
        padding: 5px 15px;
        border-radius: 30px;
        font-size: 0.8rem;
        margin-top: 1rem;
        color: white;
    }

    /* Invoice Body */
    .invoice-body {
        padding: 2rem;
    }

    /* Dark Mode Body Background */
    body.dark-mode .invoice-body {
        background: #1e1e2e;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--gray-100);
    }

    /* Dark Mode Border */
    body.dark-mode .info-grid {
        border-bottom-color: #3a3a4a;
    }

    .info-box {
        background: var(--gray-50);
        border-radius: var(--radius-md);
        padding: 1rem;
        border: 1px solid var(--gray-200);
    }

    /* Dark Mode Info Box */
    body.dark-mode .info-box {
        background: #2a2a3a;
        border-color: #3a3a4a;
    }

    .info-box .label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--gray-400);
        margin-bottom: 0.5rem;
    }

    body.dark-mode .info-box .label {
        color: #8b949e;
    }

    .info-box .value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-800);
    }

    body.dark-mode .info-box .value {
        color: #e6edf3;
    }

    .info-box .sub {
        font-size: 0.75rem;
        color: var(--gray-400);
        margin-top: 4px;
    }

    body.dark-mode .info-box .sub {
        color: #8b949e;
    }

    /* Table */
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
    }

    .invoice-table th {
        background: var(--gray-50);
        padding: 12px 15px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-500);
        border-bottom: 1.5px solid var(--gray-200);
        text-align: left;
    }

    /* Dark Mode Table Header */
    body.dark-mode .invoice-table th {
        background: #2a2a3a;
        border-bottom-color: #3a3a4a;
        color: #8b949e;
    }

    .invoice-table td {
        padding: 15px;
        border-bottom: 1px solid var(--gray-100);
        color: var(--gray-700);
    }

    /* Dark Mode Table Cells */
    body.dark-mode .invoice-table td {
        border-bottom-color: #3a3a4a;
        color: #c9d1d9;
    }

    .invoice-table tr:last-child td {
        border-bottom: none;
    }

    .total-row {
        background: var(--gray-50);
        font-weight: 700;
    }

    /* Dark Mode Total Row */
    body.dark-mode .total-row {
        background: #2a2a3a;
    }

    .total-row td {
        font-size: 1.1rem;
        color: var(--primary);
    }

    body.dark-mode .total-row td {
        color: #60a5fa;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .status-completed {
        background: #dcfce7;
        color: #166534;
    }

    /* Dark Mode Status Badge */
    body.dark-mode .status-completed {
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
    }

    .status-pending {
        background: #fef3c7;
        color: #b45309;
    }

    /* Footer */
    .invoice-footer {
        background: var(--gray-50);
        padding: 1.5rem;
        text-align: center;
        border-top: 1px solid var(--gray-200);
    }

    /* Dark Mode Footer */
    body.dark-mode .invoice-footer {
        background: #2a2a3a;
        border-top-color: #3a3a4a;
    }

    .invoice-footer p {
        color: var(--gray-500);
    }

    body.dark-mode .invoice-footer p {
        color: #8b949e;
    }

    /* Print Button */
    .print-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 1rem;
        text-decoration: none;
    }

    /* Dark Mode Print Button */
    body.dark-mode .print-btn {
        background: #3b82f6;
    }

    body.dark-mode .print-btn:hover {
        background: #2563eb;
    }

    .print-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        color: white;
    }

    .btn-back {
        background: #6c757d;
        margin-left: 10px;
    }

    body.dark-mode .btn-back {
        background: #4a4a5a;
    }

    body.dark-mode .btn-back:hover {
        background: #5a5a6a;
    }

    /* Additional Info Box - Dark Mode */
    body.dark-mode .additional-info {
        background: #2a2a3a !important;
    }

    body.dark-mode .additional-info div div:first-child {
        color: #8b949e;
    }

    body.dark-mode .additional-info div div:last-child {
        color: #e6edf3;
    }

    @media print {
        .no-print {
            display: none !important;
        }
        .invoice-wrapper {
            margin: 0;
            padding: 0;
        }
        .invoice-card {
            box-shadow: none;
        }
        body {
            background: white;
        }
        body.dark-mode .invoice-card {
            background: white;
            border: none;
        }
        body.dark-mode .invoice-header {
            background: linear-gradient(135deg, #1e5631, #2e7d32, #388e3c);
        }
        body.dark-mode .invoice-body {
            background: white;
        }
        body.dark-mode .info-box {
            background: #f8f9fc;
            border-color: #e4e7ed;
        }
        body.dark-mode .info-box .value {
            color: #111827;
        }
        body.dark-mode .invoice-table th {
            background: #f8f9fc;
            color: #6b7280;
        }
        body.dark-mode .invoice-table td {
            color: #374151;
        }
        body.dark-mode .total-row td {
            color: #2e9458;
        }
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .invoice-body {
            padding: 1.5rem;
        }
        .invoice-header {
            padding: 1.5rem;
        }
        .print-btn {
            padding: 8px 16px;
            font-size: 12px;
        }
    }
</style>

<div class="invoice-wrapper">
    <div class="no-print" style="text-align: right; margin-bottom: 1rem;">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> <?= __('print_pdf') ?>
        </button>
        <a href="javascript:history.back()" class="print-btn btn-back">
            <i class="fas fa-arrow-left"></i> <?= __('back') ?>
        </a>
    </div>

    <div class="invoice-card">
        <!-- Header -->
        <div class="invoice-header">
            <h1>
                <i class="fas fa-utensils"></i> <?= __('site_title') ?>
            </h1>
            <p><?= __('food_redistribution_network') ?></p>
            <div class="invoice-badge">
                <i class="fas fa-receipt"></i> <?= __('invoice') ?>
            </div>
        </div>

        <!-- Body -->
        <div class="invoice-body">
            <!-- Invoice Info -->
            <div class="info-grid">
                <div class="info-box">
                    <div class="label"><?= __('invoice_number') ?></div>
                    <div class="value">INV-<?= str_pad($request_id, 6, '0', STR_PAD_LEFT) ?></div>
                    <div class="sub"><?= __('generated_on') ?> <?= date('d M Y, h:i A') ?></div>
                </div>
                <div class="info-box">
                    <div class="label"><?= __('status') ?></div>
                    <div class="value">
                        <span class="status-badge status-completed">
                            <i class="fas fa-check-circle"></i> <?= __('completed') ?>
                        </span>
                    </div>
                    <div class="sub"><?= __('delivery') ?>: <?= $invoice['delivery_status'] ?></div>
                </div>
                <div class="info-box">
                    <div class="label"><?= __('payment_method') ?></div>
                    <div class="value"><?= __('cash_on_delivery') ?></div>
                    <div class="sub"><?= __('payment_collected_by_rider') ?></div>
                </div>
            </div>

            <!-- Parties Info -->
            <div class="info-grid" style="margin-bottom: 1.5rem;">
                <div class="info-box">
                    <div class="label"><i class="fas fa-hand-holding-heart"></i> <?= __('donor') ?></div>
                    <div class="value"><?= htmlspecialchars($invoice['donor_name'] ?? '—') ?></div>
                    <div class="sub"><?= htmlspecialchars($invoice['donor_email'] ?? '—') ?></div>
                    <div class="sub">📞 <?= htmlspecialchars($invoice['donor_phone'] ?? '—') ?></div>
                </div>
                <div class="info-box">
                    <div class="label"><i class="fas fa-building"></i> <?= __('ngo_receiver') ?></div>
                    <div class="value"><?= htmlspecialchars($invoice['ngo_name'] ?? '—') ?></div>
                    <div class="sub"><?= htmlspecialchars($invoice['ngo_email'] ?? '—') ?></div>
                    <div class="sub">📞 <?= htmlspecialchars($invoice['ngo_phone'] ?? '—') ?></div>
                </div>
                <div class="info-box">
                    <div class="label"><i class="fas fa-motorcycle"></i> <?= __('rider') ?></div>
                    <div class="value"><?= htmlspecialchars($invoice['rider_name'] ?? __('not_assigned')) ?></div>
                    <div class="sub"><?= htmlspecialchars($invoice['rider_email'] ?? '—') ?></div>
                    <div class="sub">📞 <?= htmlspecialchars($invoice['rider_phone'] ?? '—') ?></div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 50%;"><?= __('description') ?></th>
                        <th style="width: 25%;"><?= __('quantity') ?></th>
                        <th style="width: 25%;" class="text-end"><?= __('amount') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($invoice['food_item'] ?? __('food_item')) ?></strong><br>
                            <small style="color: var(--gray-400);"><?= __('food_donation_service') ?></small>
                        </td>
                        <td><?= htmlspecialchars($invoice['quantity'] ?? '—') ?></td>
                        <td class="text-end">Rs. <?= number_format($invoice['base_fare'] ?? 0, 2) ?></td>
                    </tr>
                    <?php if($invoice['rider_name']): ?>
                    <tr>
                        <td><strong><?= __('delivery_charges') ?></strong><br><small><?= __('rider_delivery_fee') ?></small></td>
                        <td>1</div>
                        <td class="text-end"><?= __('included') ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right; font-weight: 700;"><?= __('total_amount') ?></td>
                        <td class="text-end" style="font-size: 1.2rem;">Rs. <?= number_format($invoice['base_fare'] ?? 0, 2) ?></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Additional Info -->
            <div class="additional-info" style="background: #f0faf4; border-radius: 12px; padding: 1rem; margin-top: 1rem;">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: space-between;">
                    <div>
                        <div style="font-size: 11px; color: var(--gray-500);"><?= __('delivery_date') ?></div>
                        <div style="font-weight: 600;"><?= date('d M Y') ?></div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--gray-500);"><?= __('transaction_id') ?></div>
                        <div style="font-weight: 600;">ZH-TXN-<?= str_pad($request_id, 8, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--gray-500);"><?= __('payment_status') ?></div>
                        <div style="font-weight: 600; color: #166534;">✓ <?= __('paid_via_cod') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p style="margin: 0; font-size: 12px;">
                <i class="fas fa-heart" style="color: #2e9458;"></i> <?= __('thank_you_message') ?>
            </p>
            <p style="margin: 5px 0 0; font-size: 11px;">
                <?= __('computer_generated_receipt') ?>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>