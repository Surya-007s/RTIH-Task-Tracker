<?php
require __DIR__ . '/config/bootstrap.php';
$userId = require_login();
$allowedStatuses = ['Pending', 'In Progress', 'Completed'];
$errors = [];
$editTask = null;

// Every task write includes the signed-in owner's id. A guessed task id alone grants no access.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) { flash('error', 'That task could not be found.'); }
        else {
            $statement = $pdo->prepare('SELECT title FROM tasks WHERE id = :id AND user_id = :user_id');
            $statement->execute(['id' => $id, 'user_id' => $userId]);
            $taskToDelete = $statement->fetch();
            $statement = $pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
            $statement->execute(['id' => $id, 'user_id' => $userId]);
            flash($statement->rowCount() ? 'success' : 'error', $statement->rowCount() ? '“' . ($taskToDelete['title'] ?? 'Task') . '” was deleted.' : 'That task could not be found.');
        }
        header('Location: dashboard.php'); exit;
    }
    if ($action === 'save') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = (string) ($_POST['status'] ?? 'Pending');
        $dueDate = trim((string) ($_POST['due_date'] ?? ''));
        if ($title === '' || mb_strlen($title) > 120) $errors[] = 'Task title is required and must be 120 characters or fewer.';
        if (mb_strlen($description) > 2000) $errors[] = 'Description must be 2,000 characters or fewer.';
        if (!in_array($status, $allowedStatuses, true)) $errors[] = 'Choose a valid task status.';
        if ($dueDate !== '' && !valid_date($dueDate)) $errors[] = 'Choose a valid due date.';
        if (!$errors) {
            if ($id) {
                $statement = $pdo->prepare('UPDATE tasks SET title = :title, description = :description, status = :status, due_date = :due_date WHERE id = :id AND user_id = :user_id');
                $statement->execute(['title' => $title, 'description' => $description, 'status' => $status, 'due_date' => $dueDate ?: null, 'id' => $id, 'user_id' => $userId]);
                if (!$statement->rowCount()) {
                    $check = $pdo->prepare('SELECT id FROM tasks WHERE id = :id AND user_id = :user_id');
                    $check->execute(['id' => $id, 'user_id' => $userId]);
                    if (!$check->fetch()) { flash('error', 'That task could not be found.'); header('Location: dashboard.php'); exit; }
                }
                flash('success', '“' . $title . '” was updated. Your changes are saved.');
            } else {
                $statement = $pdo->prepare('INSERT INTO tasks (user_id, title, description, status, due_date) VALUES (:user_id, :title, :description, :status, :due_date)');
                $statement->execute(['user_id' => $userId, 'title' => $title, 'description' => $description, 'status' => $status, 'due_date' => $dueDate ?: null]);
                flash('success', '“' . $title . '” was added to your task list.');
            }
            header('Location: dashboard.php'); exit;
        }
        $editTask = ['id' => $id, 'title' => $title, 'description' => $description, 'status' => $status, 'due_date' => $dueDate];
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
if ($editId && !$editTask) {
    $statement = $pdo->prepare('SELECT id, title, description, status, due_date FROM tasks WHERE id = :id AND user_id = :user_id');
    $statement->execute(['id' => $editId, 'user_id' => $userId]);
    $editTask = $statement->fetch() ?: null;
}
$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? 'All');
if (!in_array($statusFilter, array_merge(['All'], $allowedStatuses), true)) $statusFilter = 'All';
$where = ['user_id = :user_id'];
$params = ['user_id' => $userId];
if ($search !== '') { $where[] = 'title LIKE :search'; $params['search'] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%'; }
if ($statusFilter !== 'All') { $where[] = 'status = :status'; $params['status'] = $statusFilter; }
$statement = $pdo->prepare('SELECT id, title, description, status, due_date, created_at FROM tasks WHERE ' . implode(' AND ', $where) . ' ORDER BY FIELD(status, \'In Progress\', \'Pending\', \'Completed\'), due_date IS NULL, due_date ASC, created_at DESC');
$statement->execute($params);
$tasks = $statement->fetchAll();
$summaryStatement = $pdo->prepare("SELECT COUNT(*) AS total, SUM(status = 'Pending') AS pending, SUM(status = 'In Progress') AS progress, SUM(status = 'Completed') AS completed FROM tasks WHERE user_id = :user_id");
$summaryStatement->execute(['user_id' => $userId]);
$summary = $summaryStatement->fetch();
$totalCount = (int) $summary['total'];
$pendingPercent = $totalCount ? (int) round(((int) $summary['pending'] / $totalCount) * 100) : 0;
$progressPercent = $totalCount ? (int) round(((int) $summary['progress'] / $totalCount) * 100) : 0;
$completedPercent = $totalCount ? (int) round(((int) $summary['completed'] / $totalCount) * 100) : 0;
$notice = take_flash();
$pageTitle = 'My tasks';
require __DIR__ . '/config/header.php';
?>
<section class="dashboard-head"><div><div class="eyebrow"><span class="eyebrow-dot"></span> YOUR PERSONAL WORKSPACE <span class="today-pill"><?= escape(date('l · j F Y')) ?></span></div><h1>A little progress<br><em>goes a long way.</em></h1><p>Keep your focus close. Everything you need to move forward, all in one place.</p></div><button class="button button-primary create-button" type="button" data-open-task-modal><span>＋</span> Create a task</button></section>

<section class="stats-grid" aria-label="Task summary">
  <article class="stat-card stat-total"><span class="stat-label">ALL TASKS</span><div><strong><?= $totalCount ?></strong><span class="stat-symbol">◷</span></div><small>Everything on your list</small><span class="stat-progress-track"><i style="width:100%"></i></span></article>
  <article class="stat-card"><span class="stat-label">PENDING</span><div><strong><?= (int) $summary['pending'] ?></strong><span class="stat-symbol symbol-pending">○</span></div><small>Ready when you are</small><span class="stat-progress-track"><i class="progress-pending" style="width:<?= $pendingPercent ?>%"></i></span></article>
  <article class="stat-card"><span class="stat-label">IN PROGRESS</span><div><strong><?= (int) $summary['progress'] ?></strong><span class="stat-symbol symbol-progress">◒</span></div><small>Taking shape right now</small><span class="stat-progress-track"><i class="progress-in-progress" style="width:<?= $progressPercent ?>%"></i></span></article>
  <article class="stat-card"><span class="stat-label">COMPLETED</span><div><strong><?= (int) $summary['completed'] ?></strong><span class="stat-symbol symbol-complete">✓</span></div><small>Good work, done well</small><span class="stat-progress-track"><i class="progress-completed" style="width:<?= $completedPercent ?>%"></i></span></article>
</section>

<?php if ($notice): ?><div class="toast toast-<?= escape($notice['kind']) ?>" data-toast role="<?= $notice['kind'] === 'error' ? 'alert' : 'status' ?>" aria-live="<?= $notice['kind'] === 'error' ? 'assertive' : 'polite' ?>"><span class="toast-icon" aria-hidden="true"><?= $notice['kind'] === 'success' ? '✓' : '!' ?></span><span class="toast-copy"><strong><?= $notice['kind'] === 'success' ? 'All set' : 'Couldn’t complete that' ?></strong><span><?= escape($notice['message']) ?></span></span><button type="button" class="toast-close" data-dismiss-toast aria-label="Dismiss message">×</button><span class="toast-progress" aria-hidden="true"></span></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error" role="alert"><strong>Check a couple of details</strong><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<section class="board-layout">
  <div class="task-area">
    <div class="list-heading"><div><span class="step-label">YOUR TASKS</span><h2>The work in front of you <span class="count-pill"><?= count($tasks) ?></span></h2></div><span class="sort-note">A clear next step is a good start.</span></div>
    <form class="toolbar" method="get" action="dashboard.php">
      <label class="search-box"><span aria-hidden="true">⌕</span><input type="search" name="q" value="<?= escape($search) ?>" placeholder="Search your tasks…" aria-label="Search task titles"></label>
      <label class="filter-label" for="status-filter">SHOW</label><select id="status-filter" name="status" class="filter-select" data-auto-submit aria-label="Filter tasks by status"><?php foreach (array_merge(['All'], $allowedStatuses) as $option): ?><option value="<?= escape($option) ?>" <?= $statusFilter === $option ? 'selected' : '' ?>><?= $option === 'All' ? 'All statuses' : escape($option) ?></option><?php endforeach; ?></select>
      <?php if ($search !== '' || $statusFilter !== 'All'): ?><a class="clear-link" href="dashboard.php">Clear</a><?php endif; ?>
    </form>

    <?php if (!$tasks): ?>
      <div class="empty-state"><div class="empty-illustration"><span>✓</span><i></i></div><h3><?= $search || $statusFilter !== 'All' ? 'Nothing in this view yet.' : 'Your list starts here.' ?></h3><p><?= $search || $statusFilter !== 'All' ? 'Try another search or status, or clear the filters.' : 'Add one small next step and build a little momentum.' ?></p><?php if (!$search && $statusFilter === 'All'): ?><button class="button button-primary" type="button" data-open-task-modal>Create your first task <span>↗</span></button><?php endif; ?></div>
    <?php else: ?>
      <div class="task-list">
        <?php foreach ($tasks as $task): ?>
        <article class="task-card <?= $task['status'] === 'Completed' ? 'task-done' : '' ?>">
          <div class="task-marker marker-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>"><?= $task['status'] === 'Completed' ? '✓' : '' ?></div>
          <div class="task-main"><div class="task-title-row"><h3><?= escape($task['title']) ?></h3><span class="status-badge badge-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>"><i></i><?= escape($task['status']) ?></span></div>
            <?php if ($task['description'] !== ''): ?><p><?= nl2br(escape($task['description'])) ?></p><?php endif; ?>
            <span class="due-date">◷ &nbsp;Created <?= escape(date('M j, Y', strtotime($task['created_at']))) ?></span><?php if ($task['due_date']): ?><span class="due-date <?= $task['due_date'] < date('Y-m-d') && $task['status'] !== 'Completed' ? 'overdue' : '' ?>">&nbsp; · &nbsp;<?= $task['due_date'] < date('Y-m-d') && $task['status'] !== 'Completed' ? 'Overdue ' : 'Due ' ?><?= escape(date('M j, Y', strtotime($task['due_date']))) ?></span><?php endif; ?>
          </div>
          <div class="task-actions"><button class="task-view-button" type="button" data-view-task data-title="<?= escape($task['title']) ?>" data-description="<?= escape($task['description']) ?>" data-status="<?= escape($task['status']) ?>" data-created="<?= escape(date('M j, Y', strtotime($task['created_at']))) ?>" data-due="<?= $task['due_date'] ? escape(date('M j, Y', strtotime($task['due_date']))) : '' ?>">View task <span>↗</span></button><a class="icon-action" href="dashboard.php?edit=<?= (int) $task['id'] ?>" aria-label="Edit <?= escape($task['title']) ?>" title="Edit task">✎</a><form method="post" action="dashboard.php" data-delete-form><input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $task['id'] ?>"><button class="icon-action delete-action" type="button" data-open-delete data-title="<?= escape($task['title']) ?>" aria-label="Delete <?= escape($task['title']) ?>" title="Delete task">×</button></form></div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</section>

<div class="modal-backdrop" data-task-modal <?= $editTask || $errors ? 'data-start-open="true"' : '' ?> aria-hidden="true">
  <section class="task-modal" role="dialog" aria-modal="true" aria-labelledby="task-modal-title">
    <button class="modal-close" type="button" data-close-task-modal aria-label="Close dialog">×</button>
    <div class="modal-kicker"><span class="form-icon">✳</span><span class="step-label" data-task-modal-label><?= $editTask ? 'EDIT YOUR TASK' : 'A NEW NEXT STEP' ?></span></div>
    <h2 id="task-modal-title" data-task-modal-title><?= $editTask ? 'A small adjustment.' : 'What’s on your mind?' ?></h2>
    <p class="modal-intro" data-task-modal-intro><?= $editTask ? 'Make a quick change and keep going.' : 'Give the next thing a name. You can add more detail as you go.' ?></p>
    <?php if ($errors): ?><div class="alert alert-error" role="alert"><strong>Check a couple of details</strong><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="task-form modal-task-form" method="post" action="dashboard.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($editTask['id'] ?? 0) ?>">
      <label for="task-title">Task title <span class="required">*</span></label><input class="field" id="task-title" name="title" type="text" maxlength="120" required value="<?= escape($editTask['title'] ?? '') ?>" placeholder="e.g. Prepare project notes">
      <label for="task-description">A few details <span class="optional">OPTIONAL</span></label><textarea class="field" id="task-description" name="description" rows="4" maxlength="2000" placeholder="What would be helpful to remember?"><?= escape($editTask['description'] ?? '') ?></textarea>
      <div class="form-row"><div><label for="task-status">Status</label><select class="field" id="task-status" name="status"><?php foreach ($allowedStatuses as $status): ?><option value="<?= escape($status) ?>" <?= ($editTask['status'] ?? 'Pending') === $status ? 'selected' : '' ?>><?= escape($status) ?></option><?php endforeach; ?></select></div><div><label for="task-due">Due date <span class="optional">OPTIONAL</span></label><input class="field" id="task-due" name="due_date" type="date" value="<?= escape($editTask['due_date'] ?? '') ?>"></div></div>
      <div class="modal-actions"><button class="button button-quiet" type="button" data-close-task-modal>Cancel</button><button class="button button-primary" type="submit" data-task-modal-submit><?= $editTask ? 'Save changes' : 'Create task' ?> <span>↗</span></button></div>
    </form>
  </section>
</div>
<div class="modal-backdrop" data-view-modal aria-hidden="true">
  <section class="task-modal view-task-modal" role="dialog" aria-modal="true" aria-labelledby="view-modal-title">
    <button class="modal-close" type="button" data-close-view-modal aria-label="Close task details">×</button>
    <div class="modal-kicker"><span class="form-icon">◷</span><span class="step-label">TASK DETAILS</span></div>
    <h2 id="view-modal-title" data-view-title></h2>
    <div class="view-status-line"><span class="status-badge" data-view-status></span></div>
    <div class="view-description" data-view-description></div>
    <div class="view-meta"><div><span>CREATED</span><strong data-view-created></strong></div><div data-view-due-wrap><span>DUE DATE</span><strong data-view-due></strong></div></div>
    <div class="modal-actions"><button class="button button-quiet" type="button" data-close-view-modal>Close</button></div>
  </section>
</div>
<div class="modal-backdrop delete-backdrop" data-delete-modal aria-hidden="true">
  <section class="task-modal delete-modal" role="alertdialog" aria-modal="true" aria-labelledby="delete-modal-title" aria-describedby="delete-modal-description">
    <button class="modal-close" type="button" data-close-delete aria-label="Cancel delete">×</button>
    <div class="delete-graphic" aria-hidden="true"><span class="delete-orbit orbit-one"></span><span class="delete-orbit orbit-two"></span><span class="delete-bin"><i></i><b></b></span><span class="delete-spark spark-one">✦</span><span class="delete-spark spark-two">✧</span></div>
    <span class="step-label delete-eyebrow">TASK REMOVAL</span>
    <h2 id="delete-modal-title">Delete this task?</h2>
    <p id="delete-modal-description" class="delete-message">You’re about to remove <strong data-delete-title></strong> from your list. This can’t be undone.</p>
    <div class="delete-actions"><button class="button button-quiet" type="button" data-close-delete>Keep task</button><button class="button button-danger" type="button" data-confirm-delete><span aria-hidden="true">×</span> Delete task</button></div>
  </section>
</div>
<?php require __DIR__ . '/config/footer.php'; ?>
