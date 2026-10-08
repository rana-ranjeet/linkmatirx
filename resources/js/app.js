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
        toggle.textContent = reveal ? 'Hide' : 'Show';
    });
});
