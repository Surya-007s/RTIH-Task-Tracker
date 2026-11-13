-- RTIH Taskboard — optional sample data
-- One demo account and three sample tasks (matching the delivered screenshots).
-- Import database.sql first, then this file.
--
-- Demo login: test.one@rtih.local / TestPass!123
-- (local demo password only; the hash below is a standard password_hash() bcrypt value)

USE rtih_task_tracker;

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `created_at`) VALUES
(9,'Test User One','test.one@rtih.local','$2y$10$.xGlay/c/vZkiF5rEFgoducCneJ/2iwJY6pSUjRYKLiHq5EzGgwia','2026-11-13 09:07:00');

INSERT INTO `tasks` (`id`, `user_id`, `title`, `description`, `status`, `due_date`, `created_at`, `updated_at`) VALUES
(23,9,'Quarterly report draft v2','Compile the Q3 numbers and circulate for review.','In Progress','2026-11-20','2026-11-13 09:07:00','2026-11-13 09:07:01'),
(24,9,'Update network diagram','Add the new branch office subnets before the audit.','In Progress',NULL,'2026-11-13 09:07:00','2026-11-13 09:07:00'),
(25,9,'Archive old tickets','Move everything older than 90 days into storage.','Completed',NULL,'2026-11-13 09:07:01','2026-11-13 09:07:01');
