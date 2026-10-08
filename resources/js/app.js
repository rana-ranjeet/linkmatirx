const navToggle = document.querySelector('[data-nav-toggle]');
const navMenu = document.querySelector('[data-nav-menu]');
const navBackdrop = document.querySelector('[data-drawer-close]');

const closeNavigation = () => {
    document.querySelectorAll('.nav-dropdown.is-open').forEach((dropdown) => {
        dropdown.classList.remove('is-open');
        dropdown.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
    });

    navBackdrop?.setAttribute('hidden', '');
    document.body.classList.remove('nav-drawer-open');
};

navToggle?.addEventListener('click', () => {
    const isOpen = navMenu?.classList.toggle('is-open') ?? false;
    navToggle.setAttribute('aria-expanded', String(isOpen));
});

document.querySelectorAll('[data-dropdown-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const dropdown = toggle.closest('.nav-dropdown');
        const wasOpen = dropdown?.classList.contains('is-open') ?? false;
        closeNavigation();
        if (!wasOpen) {
            dropdown?.classList.add('is-open');
            navBackdrop?.removeAttribute('hidden');
            document.body.classList.add('nav-drawer-open');
        }
        const isOpen = !wasOpen;
        toggle.setAttribute('aria-expanded', String(isOpen));
    });
});

navBackdrop?.addEventListener('click', closeNavigation);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeNavigation();
    }
});

document.querySelectorAll('.nav-dropdown-menu a').forEach((link) => {
    link.addEventListener('click', closeNavigation);
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
