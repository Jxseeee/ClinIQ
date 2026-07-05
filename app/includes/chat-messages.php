<?php foreach ($messages as $msg): ?>
<div class="chat-bubble <?= $msg['is_mine'] ? 'chat-mine' : 'chat-theirs' ?>"
     data-message-id="<?= (int) $msg['message_id'] ?>">
    <div class="chat-meta">
        <?= htmlspecialchars($msg['sender_name']) ?>
        · <?= date('M d, Y g:i A', strtotime($msg['created_at'])) ?>
    </div>
    <div class="chat-text"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
</div>
<?php endforeach; ?>
