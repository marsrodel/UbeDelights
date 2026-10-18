CREATE TABLE IF NOT EXISTS `authentication_logs` LIKE `activity_logs`;
CREATE TABLE IF NOT EXISTS `system_logs` LIKE `activity_logs`;

ALTER TABLE `activity_logs`
  ADD INDEX IF NOT EXISTS `idx_logs_role_created` (`role`, `created_at`, `id`),
  ADD INDEX IF NOT EXISTS `idx_logs_account_created` (`idNumber`, `created_at`, `id`);

ALTER TABLE `authentication_logs`
  ADD INDEX IF NOT EXISTS `idx_logs_role_created` (`role`, `created_at`, `id`),
  ADD INDEX IF NOT EXISTS `idx_logs_account_created` (`idNumber`, `created_at`, `id`);

ALTER TABLE `system_logs`
  ADD INDEX IF NOT EXISTS `idx_logs_role_created` (`role`, `created_at`, `id`),
  ADD INDEX IF NOT EXISTS `idx_logs_account_created` (`idNumber`, `created_at`, `id`);

DROP PROCEDURE IF EXISTS `migrate_log_categories`;
DELIMITER $$
CREATE PROCEDURE `migrate_log_categories`()
BEGIN
  DECLARE expected_count INT DEFAULT 0;
  DECLARE verified_count INT DEFAULT 0;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  START TRANSACTION;
  SELECT COUNT(*) INTO expected_count FROM `activity_logs` a WHERE LOWER(a.`action`) IN ('login', 'logout', 'failed_login', 'login_blocked', 'login_pending', 'login_rejected', 'session_timeout', 'unauthorized_access', 'account_lockout', 'staging_activated', 'account_activated', 'otp_requested', 'otp_resent', 'otp_verified', 'otp_failed', 'otp_expired', 'otp_locked', 'password_reset_completed');

  INSERT INTO `authentication_logs` (`id`, `idNumber`, `username`, `fullName`, `role`, `module`, `action`, `details`, `ip_address`, `created_at`, `severity`, `browser`, `device`, `os`)
  SELECT a.`id`, a.`idNumber`, a.`username`, a.`fullName`, a.`role`, a.`module`, a.`action`, a.`details`, a.`ip_address`, a.`created_at`, a.`severity`, a.`browser`, a.`device`, a.`os` FROM `activity_logs` a
  LEFT JOIN `authentication_logs` b ON b.`id` = a.`id`
  WHERE LOWER(a.`action`) IN ('login', 'logout', 'failed_login', 'login_blocked', 'login_pending', 'login_rejected', 'session_timeout', 'unauthorized_access', 'account_lockout', 'staging_activated', 'account_activated', 'otp_requested', 'otp_resent', 'otp_verified', 'otp_failed', 'otp_expired', 'otp_locked', 'password_reset_completed') AND b.`id` IS NULL;

  SELECT COUNT(*) INTO verified_count
  FROM `activity_logs` a JOIN `authentication_logs` b ON b.`id` = a.`id`
  WHERE LOWER(a.`action`) IN ('login', 'logout', 'failed_login', 'login_blocked', 'login_pending', 'login_rejected', 'session_timeout', 'unauthorized_access', 'account_lockout', 'staging_activated', 'account_activated', 'otp_requested', 'otp_resent', 'otp_verified', 'otp_failed', 'otp_expired', 'otp_locked', 'password_reset_completed')
    AND BINARY b.`id` <=> BINARY a.`id` AND
      BINARY b.`idNumber` <=> BINARY a.`idNumber` AND
      BINARY b.`username` <=> BINARY a.`username` AND
      BINARY b.`fullName` <=> BINARY a.`fullName` AND
      BINARY b.`role` <=> BINARY a.`role` AND
      BINARY b.`module` <=> BINARY a.`module` AND
      BINARY b.`action` <=> BINARY a.`action` AND
      BINARY b.`details` <=> BINARY a.`details` AND
      BINARY b.`ip_address` <=> BINARY a.`ip_address` AND
      BINARY b.`created_at` <=> BINARY a.`created_at` AND
      BINARY b.`severity` <=> BINARY a.`severity` AND
      BINARY b.`browser` <=> BINARY a.`browser` AND
      BINARY b.`device` <=> BINARY a.`device` AND
      BINARY b.`os` <=> BINARY a.`os`;

  IF verified_count <> expected_count THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Authentication log copies did not match. No historical records were removed.';
  END IF;

  DELETE a FROM `activity_logs` a JOIN `authentication_logs` b ON b.`id` = a.`id`
  WHERE LOWER(a.`action`) IN ('login', 'logout', 'failed_login', 'login_blocked', 'login_pending', 'login_rejected', 'session_timeout', 'unauthorized_access', 'account_lockout', 'staging_activated', 'account_activated', 'otp_requested', 'otp_resent', 'otp_verified', 'otp_failed', 'otp_expired', 'otp_locked', 'password_reset_completed')
    AND BINARY b.`id` <=> BINARY a.`id` AND
      BINARY b.`idNumber` <=> BINARY a.`idNumber` AND
      BINARY b.`username` <=> BINARY a.`username` AND
      BINARY b.`fullName` <=> BINARY a.`fullName` AND
      BINARY b.`role` <=> BINARY a.`role` AND
      BINARY b.`module` <=> BINARY a.`module` AND
      BINARY b.`action` <=> BINARY a.`action` AND
      BINARY b.`details` <=> BINARY a.`details` AND
      BINARY b.`ip_address` <=> BINARY a.`ip_address` AND
      BINARY b.`created_at` <=> BINARY a.`created_at` AND
      BINARY b.`severity` <=> BINARY a.`severity` AND
      BINARY b.`browser` <=> BINARY a.`browser` AND
      BINARY b.`device` <=> BINARY a.`device` AND
      BINARY b.`os` <=> BINARY a.`os`;

  COMMIT;
  SELECT expected_count AS authentication_records_migrated, verified_count AS authentication_records_verified;
END$$
DELIMITER ;
CALL `migrate_log_categories`();
DROP PROCEDURE `migrate_log_categories`;
