-- Run this if you already have the Messages table and want the automatic chat reply.
-- (The app also adds this column automatically the first time a student sends a message.)
-- When a student starts a conversation, or writes again after it was resolved, the clinic side sends one
-- automatic reply: "Hi <first name>! We received your message and expect to reply within 48 hours."
USE Clinic;

ALTER TABLE Messages
    ADD COLUMN IsAutoReply TINYINT(1) NOT NULL DEFAULT 0 AFTER Content;
