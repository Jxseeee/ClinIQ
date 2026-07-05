document.addEventListener('DOMContentLoaded', function () {
    var app = document.getElementById('chat-app');
    var presence = document.getElementById('chat-presence');
    if (!app && !presence) return;

    var source = app || presence;

    var config = {
        apiBase: app ? app.dataset.apiBase : '',
        presenceUrl: source.dataset.presenceUrl,
        queueUrl: source.dataset.queueUrl,
        authEndpoint: app ? app.dataset.authEndpoint : '',
        pusherKey: app ? app.dataset.pusherKey : '',
        pusherCluster: app ? app.dataset.pusherCluster : '',
        channel: app ? app.dataset.channel : '',
        inboxChannel: app ? (app.dataset.inboxChannel || '') : '',
        csrf: source.dataset.csrf,
        studentId: app ? app.dataset.studentId : '',
        role: app ? app.dataset.role : ''
    };

    startChatHeartbeat();

    if (!app) return;

    var list = document.getElementById('chat-messages');
    var form = document.getElementById('chat-form');
    var input = document.getElementById('chat-input');
    var seenIds = new Set();
    var sendBtn = form ? form.querySelector('button[type="submit"]') : null;

    list.querySelectorAll('[data-message-id]').forEach(function (el) {
        seenIds.add(parseInt(el.dataset.messageId, 10));
    });

    scrollToBottom();

    function scrollToBottom() {
        if (list) list.scrollTop = list.scrollHeight;
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTime(iso) {
        var normalized = iso.indexOf('T') === -1 ? iso.replace(' ', 'T') : iso;
        var date = new Date(normalized);
        if (isNaN(date.getTime())) return iso;
        return date.toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function removeEmptyState() {
        var empty = document.getElementById('chat-empty');
        if (empty) empty.remove();
        if (list) {
            list.classList.remove('is-empty');
            list.classList.add('has-messages');
        }
    }

    function startChatHeartbeat() {
        if (!config.presenceUrl || !config.csrf) return;

        function sendHeartbeat() {
            var body = new FormData();
            body.append('action', 'heartbeat');
            body.append('csrf_token', config.csrf);

            fetch(config.presenceUrl, {
                method: 'POST',
                body: body,
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.allowed === false && config.queueUrl) {
                        window.location.href = config.queueUrl;
                    }
                })
                .catch(function () { /* heartbeat retries automatically */ });
        }

        sendHeartbeat();
        setInterval(sendHeartbeat, 20000);
    }

    function appendMessage(msg, shouldScroll) {
        if (!msg || seenIds.has(msg.message_id)) return;
        seenIds.add(msg.message_id);
        removeEmptyState();

        var bubble = document.createElement('div');
        var isMine = msg.sender_role === config.role;
        bubble.className = 'chat-bubble ' + (isMine ? 'chat-mine' : 'chat-theirs');
        bubble.dataset.messageId = msg.message_id;
        bubble.innerHTML =
            '<div class="chat-meta">' + escapeHtml(msg.sender_name) +
            ' · ' + escapeHtml(formatTime(msg.created_at)) + '</div>' +
            '<div class="chat-text">' + escapeHtml(msg.content).replace(/\n/g, '<br>') + '</div>';

        list.appendChild(bubble);
        if (shouldScroll !== false) scrollToBottom();
    }

    if (input && form) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.requestSubmit();
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var content = input.value.trim();
            if (!content) return;

            if (sendBtn) sendBtn.disabled = true;

            var body = new FormData();
            body.append('content', content);
            body.append('csrf_token', config.csrf);
            if (config.role === 'admin') {
                body.append('student_id', config.studentId);
            }

            fetch(config.apiBase + '/chat.php', {
                method: 'POST',
                body: body,
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    appendMessage(data.message);
                    input.value = '';
                })
                .catch(function () {
                    alert('Failed to send message. Please try again.');
                })
                .finally(function () {
                    if (sendBtn) sendBtn.disabled = false;
                    input.focus();
                });
        });
    }

    window.refreshChatThreads = function () {
        fetch(config.apiBase + '/chat.php?action=threads', { credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.threads) return;

                var listEl = document.getElementById('chat-thread-list');
                if (!listEl) return;

                var activeId = config.studentId;
                listEl.innerHTML = data.threads.map(function (t) {
                    var unread = parseInt(t.UnreadCount, 10);
                    var preview = t.LastMessage
                        ? escapeHtml(t.LastMessage.length > 60 ? t.LastMessage.slice(0, 60) + '…' : t.LastMessage)
                        : '';
                    var badge = unread > 0
                        ? '<span class="chat-unread-badge">' + unread + '</span>'
                        : '';
                    var active = String(t.StudentID) === String(activeId) ? ' active' : '';

                    return '<li><a href="messages.php?student_id=' + t.StudentID + '"' +
                        ' class="chat-thread-item' + active + '" data-student-id="' + t.StudentID + '">' +
                        '<span class="chat-thread-name">' + escapeHtml(t.FirstName + ' ' + t.LastName) +
                        ' <small>#' + t.StudentID + '</small></span>' +
                        badge +
                        (preview ? '<span class="chat-thread-preview">' + preview + '</span>' : '') +
                        '</a></li>';
                }).join('');
            })
            .catch(function () { /* ignore sidebar refresh errors */ });
    };

    if (!config.pusherKey) return;

    var pusherScript = document.createElement('script');
    pusherScript.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
    pusherScript.onload = function () {
        var pusher = new Pusher(config.pusherKey, {
            cluster: config.pusherCluster,
            authEndpoint: config.authEndpoint,
            auth: {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }
        });

        var chatChannel = pusher.subscribe(config.channel);
        chatChannel.bind('new-message', function (data) {
            appendMessage(data);
        });

        if (config.inboxChannel && config.role === 'admin') {
            var inbox = pusher.subscribe(config.inboxChannel);
            inbox.bind('inbox-update', function () {
                window.refreshChatThreads();
            });
        }
    };
    document.head.appendChild(pusherScript);
});
