// Request permission for push notifications
function requestNotificationPermission() {
    if ('Notification' in window && 'serviceWorker' in navigator) {
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                registerServiceWorker();
            }
        });
    }
}

// Register service worker
function registerServiceWorker() {
    navigator.serviceWorker.register('/Zero%20Hunger/Frontend/service-worker.js')
        .then(function(registration) {
            console.log('Service Worker registered');
            subscribeUserToPush(registration);
        })
        .catch(function(err) {
            console.log('Service Worker registration failed:', err);
        });
}

// Subscribe to push notifications
function subscribeUserToPush(registration) {
    registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array('YOUR_VAPID_PUBLIC_KEY')
    }).then(function(subscription) {
        // Send subscription to server
        fetch('/Zero%20Hunger/Backend/save_subscription.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(subscription)
        });
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Call this when user logs in
if (document.getElementById('enableNotifications')) {
    document.getElementById('enableNotifications').addEventListener('click', requestNotificationPermission);
}