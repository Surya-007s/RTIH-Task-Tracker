<?php
require __DIR__ . '/config/bootstrap.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $statement = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: dashboard.php'); exit;
    }
    $error = 'That email and password combination was not recognized.';
}
$pageTitle = 'Sign in';
require __DIR__ . '/config/header.php';
?>
<section class="auth-layout">
  <div class="auth-story"><div class="eyebrow"><span class="eyebrow-dot"></span> YOUR WORKSPACE, IN FOCUS</div><h1>Make room for<br><em>good work.</em></h1><p>A calmer way to keep every task moving. Pick up where you left off and focus on what matters today.</p><div class="story-art" aria-hidden="true"><span class="art-sun"></span><span class="art-line line-one"></span><span class="art-line line-two"></span><span class="art-leaf leaf-one"></span><span class="art-leaf leaf-two"></span><span class="art-dot dot-one"></span><span class="art-dot dot-two"></span><span class="art-dot dot-three"></span><span class="art-card"><b>A little progress adds up.</b><small>One thing at a time.</small><i>↗</i></span></div><div class="story-caption"><span>01 — YOUR SPACE</span><span>DESIGNED FOR CLARITY</span></div></div>
  <div class="auth-panel"><div class="panel-heading"><span class="step-label">WELCOME BACK</span><h2>Good work starts here.</h2><p>Sign in to see your tasks and keep things moving.</p></div>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><strong>Unable to sign in</strong><p><?= escape($error) ?></p></div><?php endif; ?>
    <form class="auth-form" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
      <label for="email">Email address</label><input class="field" id="email" name="email" type="email" autocomplete="email" maxlength="190" required value="<?= escape($email) ?>" placeholder="you@example.com">
      <label for="password">Password</label><div class="password-wrap"><input class="field" id="password" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password"><button class="reveal" type="button" data-toggle-password="password" aria-label="Show password">Show</button></div>
      <button class="button button-primary button-wide" type="submit">Sign in to your workspace <span>↗</span></button>
    </form>
    <p class="auth-switch">New to RTIH Task Tracker? <a href="register.php">Create an account</a></p>
    <p class="privacy-note"><span>✳</span> Your tasks are private to your account.</p>
  </div>
</section>
<?php require __DIR__ . '/config/footer.php'; ?>
