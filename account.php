<?php
require __DIR__ . '/config/bootstrap.php';
$userId = require_login();
$errors = [];
$notice = take_flash();
$statement = $pdo->prepare('SELECT id, name, email, password_hash, created_at FROM users WHERE id = :id LIMIT 1');
$statement->execute(['id' => $userId]);
$user = $statement->fetch();
if (!$user) { $_SESSION = []; session_destroy(); header('Location: login.php'); exit; }

$newEmail = $user['email'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    if (!password_verify($currentPassword, $user['password_hash'])) {
        $errors[] = 'Your current password is incorrect. No account changes were made.';
    } elseif ($action === 'email') {
        $newEmail = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL) || mb_strlen($newEmail) > 190) {
            $errors[] = 'Enter a valid email address (190 characters or fewer).';
        } elseif ($newEmail === $user['email']) {
            $errors[] = 'That is already the email address on your account.';
        } else {
            try {
                $update = $pdo->prepare('UPDATE users SET email = :email WHERE id = :id');
                $update->execute(['email' => $newEmail, 'id' => $userId]);
                flash('success', 'Your email address was updated.');
                header('Location: account.php'); exit;
            } catch (PDOException $error) {
                if ($error->getCode() === '23000') $errors[] = 'That email address is already used by another account.';
                else { error_log('Email update failed: ' . $error->getMessage()); $errors[] = 'We could not update your email right now. Please try again.'; }
            }
        }
    } elseif ($action === 'password') {
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        if (strlen($newPassword) < 8 || strlen($newPassword) > 72) $errors[] = 'Choose a new password between 8 and 72 characters.';
        if ($newPassword !== $confirmPassword) $errors[] = 'The new password and confirmation do not match.';
        if ($newPassword === $currentPassword) $errors[] = 'Choose a password different from your current password.';
        if (!$errors) {
            $update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $update->execute(['hash' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $userId]);
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            flash('success', 'Your password was changed. Use the new password next time you sign in.');
            header('Location: account.php'); exit;
        }
    } else {
        $errors[] = 'Choose an account setting to update.';
    }
}

$pageTitle = 'Account settings';
require __DIR__ . '/config/header.php';
?>
<section class="settings-head">
  <a class="back-link" href="dashboard.php">← &nbsp;Back to my tasks</a>
  <div class="eyebrow"><span class="eyebrow-dot"></span> YOUR ACCOUNT</div>
  <h1>Account <em>settings.</em></h1>
  <p>Manage your sign-in details. We’ll ask for your current password before saving any changes.</p>
</section>

<?php if ($notice): ?><div class="toast toast-<?= escape($notice['kind']) ?>" data-toast role="<?= $notice['kind'] === 'error' ? 'alert' : 'status' ?>" aria-live="polite"><span class="toast-icon" aria-hidden="true">✓</span><span class="toast-copy"><strong>All set</strong><span><?= escape($notice['message']) ?></span></span><button type="button" class="toast-close" data-dismiss-toast aria-label="Dismiss message">×</button><span class="toast-progress" aria-hidden="true"></span></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error settings-error" role="alert"><strong>We couldn’t save that yet</strong><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<section class="settings-layout">
  <aside class="account-summary">
    <span class="account-avatar"><?= escape(strtoupper(substr($user['name'], 0, 1))) ?></span>
    <span class="step-label">SIGNED IN AS</span>
    <strong><?= escape($user['name']) ?></strong>
    <span class="account-email"><?= escape($user['email']) ?></span>
    <span class="account-since">Member since <?= escape(date('M Y', strtotime($user['created_at']))) ?></span>
  </aside>

  <div class="settings-cards">
    <article class="settings-card">
      <div class="settings-card-heading"><span class="settings-icon">✉</span><div><h2>Email address</h2><p>Use an address you can access to sign in.</p></div></div>
      <form class="settings-form" method="post" action="account.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="email">
        <label for="account-email">New email address</label><input class="field" id="account-email" name="email" type="email" autocomplete="email" maxlength="190" required value="<?= escape($newEmail) ?>">
        <label for="email-current-password">Confirm with current password</label><input class="field" id="email-current-password" name="current_password" type="password" autocomplete="current-password" required placeholder="Enter your current password">
        <button class="button button-primary" type="submit">Update email <span>↗</span></button>
      </form>
    </article>

    <article class="settings-card">
      <div class="settings-card-heading"><span class="settings-icon">⌑</span><div><h2>Password</h2><p>Choose a new password you haven’t used here before.</p></div></div>
      <form class="settings-form" method="post" action="account.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="password">
        <label for="password-current">Current password</label><input class="field" id="password-current" name="current_password" type="password" autocomplete="current-password" required placeholder="Enter your current password">
        <div class="settings-two-fields"><div><label for="new-password">New password <span class="label-hint">8 characters or more</span></label><input class="field" id="new-password" name="new_password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required placeholder="Create a new password"></div><div><label for="confirm-password">Confirm new password</label><input class="field" id="confirm-password" name="confirm_password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required placeholder="Enter it again"></div></div>
        <button class="button button-primary" type="submit">Change password <span>↗</span></button>
      </form>
    </article>
  </div>
</section>
<?php require __DIR__ . '/config/footer.php'; ?>
