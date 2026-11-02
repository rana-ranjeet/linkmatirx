const navToggle = document.querySelector('[data-nav-toggle]');
const navMenu = document.querySelector('[data-nav-menu]');

const closeNavigation = ({ closeMenu = false } = {}) => {
    document.querySelectorAll('.nav-dropdown.is-open').forEach((dropdown) => {
        dropdown.classList.remove('is-open');
        dropdown.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
    });

    if (closeMenu) {
        navMenu?.classList.remove('is-open');
        navToggle?.setAttribute('aria-expanded', 'false');
    }
};

navToggle?.addEventListener('click', () => {
    const isOpen = navMenu?.classList.toggle('is-open') ?? false;
    navToggle.setAttribute('aria-expanded', String(isOpen));
    if (!isOpen) {
        closeNavigation();
    }
});

document.querySelectorAll('[data-dropdown-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const dropdown = toggle.closest('.nav-dropdown');
        const wasOpen = dropdown?.classList.contains('is-open') ?? false;
        closeNavigation();
        if (!wasOpen) {
            dropdown?.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });
});

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Node)) {
        return;
    }

    if (!navMenu?.contains(event.target) && !navToggle?.contains(event.target)) {
        closeNavigation({ closeMenu: navMenu?.classList.contains('is-open') ?? false });
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        const hadOpenDropdown = document.querySelector('.nav-dropdown.is-open');
        const menuIsOpen = navMenu?.classList.contains('is-open') ?? false;
        closeNavigation({ closeMenu: menuIsOpen });
        if (menuIsOpen) {
            navToggle?.focus();
        } else if (hadOpenDropdown instanceof HTMLElement) {
            hadOpenDropdown.querySelector('[data-dropdown-toggle]')?.focus();
        }
    }
});

navMenu?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => closeNavigation({ closeMenu: true }));
});

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const input = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(reveal));
        toggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
        toggle.textContent = reveal ? 'Hide' : 'Show';
    });
});

const buyerOnlyFields = document.querySelectorAll('[data-buyer-only]');
const sellerOnlyFields = document.querySelectorAll('[data-seller-only]');
const monthlyBudget = document.getElementById('monthly_budget');
const registrationRoles = document.querySelectorAll('input[name="role"]');
const registrationEmailLabel = document.querySelector('[data-contact-email-label]');
const registrationEmail = document.getElementById('email');
const registrationSubmit = document.querySelector('[data-register-submit]');

const updateRegistrationRole = () => {
    const isBuying = document.querySelector('input[name="role"]:checked')?.value !== 'publisher';

    buyerOnlyFields.forEach((field) => {
        field.hidden = !isBuying;
        field.querySelectorAll('input, select, textarea').forEach((control) => {
            control.disabled = !isBuying;
        });
    });

    sellerOnlyFields.forEach((field) => {
        field.hidden = isBuying;
        field.querySelectorAll('input, select, textarea').forEach((control) => {
            control.disabled = isBuying;
        });
    });

    if (monthlyBudget instanceof HTMLSelectElement) {
        monthlyBudget.required = isBuying;
    }

    if (registrationEmailLabel instanceof HTMLElement && registrationEmail instanceof HTMLInputElement) {
        registrationEmailLabel.textContent = isBuying ? 'Email' : 'Contact email';
        registrationEmail.placeholder = isBuying ? 'Your email' : 'Contact email';
    }

    if (registrationSubmit instanceof HTMLButtonElement) {
        registrationSubmit.textContent = isBuying ? 'Create account' : 'Request access';
        registrationSubmit.classList.toggle('register-submit-request-access', !isBuying);
    }
};

registrationRoles.forEach((role) => {
    role.addEventListener('change', updateRegistrationRole);
});

updateRegistrationRole();

const richEditor = document.querySelector('[data-rich-editor]');
const richEditorSource = document.querySelector('[data-rich-editor-source]');
const richEditorForm = richEditor?.closest('form');

if (richEditor instanceof HTMLElement && richEditorSource instanceof HTMLTextAreaElement && richEditorForm instanceof HTMLFormElement) {
    if (!richEditor.innerHTML.trim() && richEditorSource.value) {
        richEditor.textContent = richEditorSource.value;
    }
    richEditorSource.value = richEditor.innerHTML;
    richEditorSource.classList.add('visually-hidden');
    richEditor.setAttribute('contenteditable', 'true');

    document.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            const command = button.dataset.editorCommand;
            let value = button.dataset.editorValue ?? null;

            if (command === 'createLink' && value === 'prompt') {
                value = window.prompt('Enter a link URL');
                if (!value) {
                    return;
                }
            }

            richEditor.focus();
            document.execCommand(command ?? '', false, value);
            richEditorSource.value = richEditor.innerHTML;
        });
    });

    richEditor.addEventListener('input', () => {
        richEditorSource.value = richEditor.innerHTML;
    });

    richEditorForm.addEventListener('submit', () => {
        richEditorSource.value = richEditor.innerHTML;
    });
}
