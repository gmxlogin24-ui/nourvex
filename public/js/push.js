(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = atob(base64);
        return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)));
    }

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function subscribeUser(registration) {
        const vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
        if (!vapidMeta || !vapidMeta.content) return;

        registration.pushManager.getSubscription().then(function (existing) {
            if (existing) return;

            if (Notification.permission === 'denied') return;

            Notification.requestPermission().then(function (permission) {
                if (permission !== 'granted') return;

                registration.pushManager
                    .subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidMeta.content),
                    })
                    .then(function (subscription) {
                        fetch('/push/subscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken(),
                                Accept: 'application/json',
                            },
                            body: JSON.stringify(subscription.toJSON()),
                        });
                    })
                    .catch(function (err) {
                        console.warn('Push subscribe failed:', err);
                    });
            });
        });
    }

    window.addEventListener('load', function () {
        navigator.serviceWorker
            .register('/sw.js')
            .then(subscribeUser)
            .catch(function (err) {
                console.warn('Service worker registration failed:', err);
            });
    });
})();
