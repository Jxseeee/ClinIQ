document.addEventListener('DOMContentLoaded', function () {
    var queue = document.getElementById('chat-queue');
    if (!queue) return;

    var statusUrl = queue.dataset.statusUrl;
    var activeCount = document.getElementById('queue-active-count');
    var position = document.getElementById('queue-position');

    function checkQueue() {
        fetch(statusUrl + '?action=status', { credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.allowed && data.return_url) {
                    window.location.href = data.return_url;
                    return;
                }

                if (activeCount && typeof data.active_count !== 'undefined') {
                    activeCount.textContent = data.active_count;
                }
                if (position && data.position) {
                    position.textContent = data.position;
                }
            })
            .catch(function () { /* keep polling */ });
    }

    checkQueue();
    setInterval(checkQueue, 5000);
});
