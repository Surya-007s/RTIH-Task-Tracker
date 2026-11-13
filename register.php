<?php
require __DIR__ . '/config/bootstrap.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$errors = [];
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || mb_strlen($name) > 80) $errors[] = 'Enter a name under 80 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8 || strlen($password) > 72) $errors[] = 'Choose a password between 8 and 72 characters.';
    if (!$errors) {
        try {
            $statement = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash)');
            $statement->execute(['name' => $name, 'email' => $email, 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: dashboard.php'); exit;
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') $errors[] = 'An account with that email already exists.';
            else { error_log('Registration failed: ' . $error->getMessage()); $errors[] = 'Registration could not be completed. Please try again.'; }
        }
    }
}
$pageTitle = 'Create account';
require __DIR__ . '/config/header.php';
?>
<section class="auth-layout">
  <div class="auth-story"><div class="eyebrow"><span class="eyebrow-dot"></span> YOUR WORKSPACE, IN FOCUS</div><h1>Make room for<br><em>good work.</em></h1><p>A calmer way to keep every task moving. Create your space and start with what matters today.</p><div class="story-art" aria-hidden="true"><span class="art-sun"></span><span class="art-line line-one"></span><span class="art-line line-two"></span><span class="art-leaf leaf-one"></span><span class="art-leaf leaf-two"></span><span class="art-dot dot-one"></span><span class="art-dot dot-two"></span><span class="art-dot dot-three"></span><span class="art-card"><b>Today, thoughtfully.</b><small>Your next step is ready.</small><i>↗</i></span></div><div class="story-caption"><span>01 — GET STARTED</span><span>DESIGNED FOR CLARITY</span></div></div>
  <div class="auth-panel"><div class="panel-heading"><span class="step-label">CREATE YOUR ACCOUNT</span><h2>Start with a clear mind.</h2><p>It only takes a moment to set up your workspace.</p></div>
    <?php if ($errors): ?><div class="alert alert-error" role="alert"><strong>Almost there</strong><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="auth-form" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
      <label for="name">Your name</label><input class="field" id="name" name="name" type="text" autocomplete="name" maxlength="80" required value="<?= escape($name) ?>" placeholder="e.g. Ananya Rao">
      <label for="email">Email address</label><input class="field" id="email" name="email" type="email" autocomplete="email" maxlength="190" required value="<?= escape($email) ?>" placeholder="you@example.com">
      <label for="password">Password <span class="label-hint">8 characters or more</span></label><div class="password-wrap"><input class="field" id="password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required placeholder="Create a strong password"><button class="reveal" type="button" data-toggle-password="password" aria-label="Show password">Show</button></div>
      <button class="button button-primary button-wide" type="submit">Create my workspace <span>↗</span></button>
    </form>
    <p class="auth-switch">Already have a workspace? <a href="login.php">Sign in</a></p>
    <p class="privacy-note"><span>✳</span> Your tasks are private to your account.</p>
  </div>
</section>
<?php require __DIR__ . '/config/footer.php'; ?>
