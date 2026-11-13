document.addEventListener('DOMContentLoaded', () => {
  // Keep fixed dialogs outside animated/transformed page containers. A transform
  // creates a new containing block and can otherwise offset the popup on screen.
  document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
    if (backdrop.parentElement !== document.body) document.body.appendChild(backdrop);
  });

  const taskModal = document.querySelector('[data-task-modal]');
  const viewModal = document.querySelector('[data-view-modal]');
  const deleteModal = document.querySelector('[data-delete-modal]');
  const openTaskModal = document.querySelectorAll('[data-open-task-modal]');
  let previousFocus = null;

  const resetTaskModal = () => {
    if (!taskModal) return;
    const form = taskModal.querySelector('.modal-task-form');
    if (!form) return;
    form.querySelector('[name="id"]').value = '0';
    form.querySelector('[name="title"]').value = '';
    form.querySelector('[name="description"]').value = '';
    form.querySelector('[name="status"]').value = 'Pending';
    form.querySelector('[name="due_date"]').value = '';
    taskModal.querySelector('[data-task-modal-label]').textContent = 'A NEW NEXT STEP';
    taskModal.querySelector('[data-task-modal-title]').textContent = 'What’s on your mind?';
    taskModal.querySelector('[data-task-modal-intro]').textContent = 'Give the next thing a name. You can add more detail as you go.';
    taskModal.querySelector('[data-task-modal-submit]').firstChild.nodeValue = 'Create task ';
    taskModal.querySelector('.alert-error')?.remove();
    const url = new URL(window.location.href);
    if (url.searchParams.has('edit')) {
      url.searchParams.delete('edit');
      window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`);
    }
  };

  const openModal = () => {
    if (!taskModal) return;
    previousFocus = document.activeElement;
    taskModal.classList.add('is-open');
    taskModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    window.setTimeout(() => taskModal.querySelector('#task-title')?.focus(), 80);
  };

  const closeModal = () => {
    if (!taskModal) return;
    taskModal.classList.remove('is-open');
    taskModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    resetTaskModal();
    previousFocus?.focus();
  };

  openTaskModal.forEach((button) => button.addEventListener('click', () => {
    resetTaskModal();
    openModal();
  }));
  taskModal?.querySelectorAll('[data-close-task-modal]').forEach((button) => button.addEventListener('click', closeModal));
  taskModal?.addEventListener('click', (event) => {
    if (event.target === taskModal) closeModal();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && taskModal?.classList.contains('is-open')) closeModal();
    if (event.key === 'Escape' && viewModal?.classList.contains('is-open')) closeViewModal();
    if (event.key === 'Escape' && deleteModal?.classList.contains('is-open')) closeDeleteModal();
  });

  if (taskModal?.dataset.startOpen === 'true') openModal();

  // Make task edit links reopen the same dialog with the task selected by the server.
  if (new URLSearchParams(window.location.search).has('edit')) openModal();

  document.querySelectorAll('[data-auto-submit]').forEach((control) => {
    control.addEventListener('change', () => control.form?.requestSubmit());
  });

  document.querySelectorAll('[data-toast]').forEach((toast) => {
    let dismissTimer;
    let isDismissing = false;
    const dismiss = () => {
      if (isDismissing) return;
      isDismissing = true;
      window.clearTimeout(dismissTimer);
      toast.classList.add('toast-leaving');
      window.setTimeout(() => toast.remove(), 220);
    };
    toast.querySelector('[data-dismiss-toast]')?.addEventListener('click', dismiss);
    dismissTimer = window.setTimeout(dismiss, 5000);
  });

  let viewPreviousFocus = null;
  let deletePreviousFocus = null;
  let pendingDeleteForm = null;
  const closeDeleteModal = () => {
    if (!deleteModal) return;
    deleteModal.classList.remove('is-open');
    deleteModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    pendingDeleteForm = null;
    deletePreviousFocus?.focus();
  };
  document.querySelectorAll('[data-open-delete]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!deleteModal) return;
      pendingDeleteForm = button.closest('[data-delete-form]');
      deletePreviousFocus = button;
      deleteModal.querySelector('[data-delete-title]').textContent = `“${button.dataset.title || 'this task'}”`;
      deleteModal.classList.add('is-open');
      deleteModal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      deleteModal.querySelector('[data-confirm-delete]')?.focus();
    });
  });
  deleteModal?.querySelectorAll('[data-close-delete]').forEach((button) => button.addEventListener('click', closeDeleteModal));
  deleteModal?.addEventListener('click', (event) => {
    if (event.target === deleteModal) closeDeleteModal();
  });
  deleteModal?.querySelector('[data-confirm-delete]')?.addEventListener('click', () => {
    if (pendingDeleteForm) pendingDeleteForm.requestSubmit();
  });

  const closeViewModal = () => {
    if (!viewModal) return;
    viewModal.classList.remove('is-open');
    viewModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    viewPreviousFocus?.focus();
  };
  document.querySelectorAll('[data-view-task]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!viewModal) return;
      viewPreviousFocus = button;
      viewModal.querySelector('[data-view-title]').textContent = button.dataset.title || '';
      const description = viewModal.querySelector('[data-view-description]');
      description.textContent = button.dataset.description || 'No additional details were added.';
      description.classList.toggle('is-empty', !button.dataset.description);
      const badge = viewModal.querySelector('[data-view-status]');
      const status = button.dataset.status || 'Pending';
      const statusClass = status.toLowerCase().replace(/\s+/g, '-');
      badge.className = `status-badge badge-${statusClass}`;
      badge.replaceChildren(Object.assign(document.createElement('i'), { 'aria-hidden': 'true' }), document.createTextNode(status));
      viewModal.querySelector('[data-view-created]').textContent = button.dataset.created || '';
      viewModal.querySelector('[data-view-due]').textContent = button.dataset.due || 'No due date';
      viewModal.querySelector('[data-view-due-wrap]').classList.toggle('is-empty', !button.dataset.due);
      viewModal.classList.add('is-open');
      viewModal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      viewModal.querySelector('[data-close-view-modal]')?.focus();
    });
  });
  viewModal?.querySelectorAll('[data-close-view-modal]').forEach((button) => button.addEventListener('click', closeViewModal));
  viewModal?.addEventListener('click', (event) => {
    if (event.target === viewModal) closeViewModal();
  });

  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const field = document.getElementById(button.dataset.togglePassword);
      const show = field.type === 'password';
      field.type = show ? 'text' : 'password';
      button.textContent = show ? 'Hide' : 'Show';
      button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });

  document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
  });

  document.querySelectorAll('form[novalidate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const title = form.querySelector('[name="title"]');
      if (title && !title.value.trim()) {
        event.preventDefault();
        title.setCustomValidity('Please enter a task title.');
        title.reportValidity();
        title.addEventListener('input', () => title.setCustomValidity(''), { once: true });
      }
    });
  });
});
