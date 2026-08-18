document.addEventListener('DOMContentLoaded', function () {
    var queue = document.getElementById('chat-queue');
    if (!queue) return;

    var statusUrl = queue.dataset.statusUrl;
    var activeCount = document.getElementById('queue-active-count');
    var position = document.getElementById('queue-position');
    var pollInterval = 5000;
    var pollTimer = null;
    var isChecking = false;
    var isRedirecting = false;

    function scheduleNextCheck() {
        if (isRedirecting) return;
        window.clearTimeout(pollTimer);
        pollTimer = window.setTimeout(checkQueue, pollInterval);
    }

    function checkQueue() {
        if (isChecking || isRedirecting) return;
        isChecking = true;

        fetch(statusUrl + '?action=status&_=' + Date.now(), {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Queue status request failed.');
                return res.json();
            })
            .then(function (data) {
                if (data.allowed && data.return_url) {
                    isRedirecting = true;
                    window.location.replace(data.return_url);
                    return;
                }

                if (activeCount && typeof data.active_count !== 'undefined') {
                    activeCount.textContent = data.active_count;
                }
                if (position && data.position) {
                    position.textContent = data.position;
                }
            })
            .catch(function () { /* the full-page refresh remains as fallback */ })
            .finally(function () {
                isChecking = false;
                scheduleNextCheck();
            });
    }

    checkQueue();

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            window.clearTimeout(pollTimer);
            checkQueue();
        }
    });

    window.addEventListener('online', checkQueue);
});
