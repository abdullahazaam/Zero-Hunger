<?php
// ============================================
// EMAIL CONFIGURATION (FREE - SMTP)
// ============================================
// Use Gmail or your hosting email
define('SMTP_HOST', 'smtp.gmail.com');      // For Gmail
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com'); // CHANGE THIS
define('SMTP_PASS', 'your-app-password');    // CHANGE THIS (Gmail App Password)
define('SMTP_FROM', 'noreply@zerohunger.com');
define('SMTP_FROM_NAME', 'Zero Hunger Network');

// ============================================
// GOOGLE ANALYTICS (FREE)
// ============================================
// Get from: analytics.google.com
define('GA_MEASUREMENT_ID', 'G-XXXXXXXXXX'); // CHANGE THIS

// ============================================
// PUSH NOTIFICATIONS (FREE)
// ============================================
// VAPID keys for Web Push (generate once)
define('VAPID_PUBLIC_KEY', 'your-public-key');
define('VAPID_PRIVATE_KEY', 'your-private-key');
define('VAPID_SUBJECT', 'mailto:support@zerohunger.com');

// ============================================
// SITE URL
// ============================================
define('SITE_URL', 'http://localhost/Zero%20Hunger/Frontend/');
define('ADMIN_URL', 'http://localhost/Zero%20Hunger/Backend/');
define('SITE_NAME', 'Zero Hunger');

// ============================================
// PDF SETTINGS
// ============================================
define('INVOICE_PATH', '../uploads/invoices/');
?>