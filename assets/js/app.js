/* ==========================================================================
   ObscuraX — public interactions
   All hooks are data-attribute driven so CSP can stay strict.
   ========================================================================== */
(function () {
    'use strict';

    const csrf = () => (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    const base = () => (document.querySelector('meta[name="base-url"]') || {}).content || '';

    function flash(message, type) {
        let stack = document.querySelector('.flash-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'flash-stack';
            document.body.appendChild(stack);
        }
        const icon = type === 'error' ? 'bi-exclamation-octagon' : (type === 'info' ? 'bi-info-circle' : 'bi-check-circle');
        const node = document.createElement('div');
        node.className = 'flash flash-' + (type || 'success');
        node.setAttribute('role', 'status');
        node.innerHTML = '<i class="bi ' + icon + '"></i><div>' + message + '</div>' +
            '<button type="button" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>';
        stack.appendChild(node);
        autoDismiss(node);
    }

    function autoDismiss(node) {
        const close = () => {
            node.style.opacity = '0';
            node.style.transform = 'translateY(-6px)';
            setTimeout(() => node.remove(), 250);
        };
        const btn = node.querySelector('button');
        if (btn) btn.addEventListener('click', close);
        setTimeout(close, 6500);
    }

    /* ------------------------------------------------------------- header */
    const header = document.querySelector('.site-header');
    if (header) {
        const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ------------------------------------------------------- flash toasts */
    document.querySelectorAll('.flash-stack .flash').forEach(autoDismiss);

    /* ------------------------------------------------ reveal on scroll */
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (revealEls.length) {
        if (!('IntersectionObserver' in window)) {
            revealEls.forEach((el) => el.classList.add('is-visible'));
        } else {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px' });
            revealEls.forEach((el) => io.observe(el));
        }
    }

    /* --------------------------------------------------- article progress */
    const progress = document.querySelector('[data-reading-progress]');
    const articleBody = document.querySelector('.article-body');
    if (progress && articleBody) {
        const update = () => {
            const rect = articleBody.getBoundingClientRect();
            const total = rect.height - window.innerHeight * 0.4;
            const scrolled = Math.min(Math.max(-rect.top + 120, 0), Math.max(total, 1));
            progress.style.width = Math.min(100, (scrolled / Math.max(total, 1)) * 100) + '%';
        };
        update();
        window.addEventListener('scroll', update, { passive: true });
    }

    /* -------------------------------------------------------------- likes */
    document.addEventListener('click', async (event) => {
        const btn = event.target.closest('[data-like]');
        if (!btn) return;
        event.preventDefault();

        if (btn.dataset.auth !== '1') {
            window.location.href = btn.dataset.loginUrl || (base() + '/login');
            return;
        }
        if (btn.disabled) return;
        btn.disabled = true;

        try {
            const response = await fetch(btn.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (!response.ok || !data.ok) {
                flash(data.message || 'Could not update your like.', 'error');
                return;
            }
            btn.classList.toggle('is-liked', !!data.liked);
            btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
            const count = btn.querySelector('[data-like-count]');
            if (count) count.textContent = data.count;
            const label = btn.querySelector('[data-like-label]');
            if (label) label.textContent = data.liked ? 'Liked' : 'Like';
            document.querySelectorAll('[data-like][data-url="' + btn.dataset.url + '"]').forEach((other) => {
                if (other === btn) return;
                other.classList.toggle('is-liked', !!data.liked);
                other.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                const otherCount = other.querySelector('[data-like-count]');
                if (otherCount) otherCount.textContent = data.count;
                const otherLabel = other.querySelector('[data-like-label]');
                if (otherLabel) otherLabel.textContent = data.liked ? 'Liked' : 'Like';
                const icon = other.querySelector('i');
                if (icon) icon.className = 'bi ' + (data.liked ? 'bi-heart-fill' : 'bi-heart') + (other.classList.contains('btn') ? ' me-1' : '');
            });
            const icon = btn.querySelector('i');
            if (icon) icon.className = 'bi ' + (data.liked ? 'bi-heart-fill' : 'bi-heart') + (btn.classList.contains('btn') ? ' me-1' : '');
            flash(data.message, 'success');
        } catch (error) {
            flash('Network error. Please try again.', 'error');
        } finally {
            btn.disabled = false;
        }
    });

    /* --------------------------------------------------------- newsletter */
    document.querySelectorAll('form[data-newsletter]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            const note = form.parentElement.querySelector('[data-newsletter-msg]');
            const original = button ? button.innerHTML : '';
            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(form)
                });
                const data = await response.json();
                if (note) {
                    note.textContent = data.message || '';
                    note.className = 'newsletter-note ' + (data.ok ? 'text-success' : 'text-danger');
                }
                flash(data.message || 'Thank you.', data.ok ? 'success' : 'error');
                if (data.ok) form.reset();
            } catch (error) {
                flash('Network error. Please try again.', 'error');
            } finally {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = original;
                }
            }
        });
    });

    /* --------------------------------------------------- password helpers */
    document.querySelectorAll('[data-reveal-target]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.querySelector(btn.dataset.revealTarget);
            if (!input) return;
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            const icon = btn.querySelector('i');
            if (icon) icon.className = 'bi ' + (visible ? 'bi-eye' : 'bi-eye-slash');
        });
    });

    document.querySelectorAll('[data-strength]').forEach((input) => {
        const meter = document.querySelector(input.dataset.strength);
        const hint = document.querySelector(input.dataset.strengthHint || '');
        if (!meter) return;
        const evaluate = () => {
            const value = input.value;
            let score = 0;
            if (value.length >= 8) score++;
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
            if (/\d/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value) && value.length >= 10) score++;
            meter.dataset.score = String(score);
            if (hint) {
                hint.textContent = ['Use 8+ characters with letters, numbers and a symbol.',
                    'Weak password.',
                    'Fair password.',
                    'Good password.',
                    'Strong password.'][score];
            }
        };
        input.addEventListener('input', evaluate);
        evaluate();
    });

    /* ------------------------------------------------------ comment replies */
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-reply-toggle]');
        if (!toggle) return;
        const target = document.getElementById(toggle.dataset.replyToggle);
        if (!target) return;
        const open = target.classList.toggle('is-open');
        if (open) {
            const field = target.querySelector('textarea');
            if (field) field.focus();
        }
    });

    /* ------------------------------------------------------- confirm forms */
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });

    /* ------------------------------------------------------------ copy link */
    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const text = btn.dataset.copy;
            try {
                await navigator.clipboard.writeText(text);
                const original = btn.getAttribute('title') || 'Copy';
                btn.setAttribute('title', 'Copied');
                btn.classList.add('is-liked');
                flash('Link copied to your clipboard.', 'success');
                setTimeout(() => {
                    btn.setAttribute('title', original);
                    btn.classList.remove('is-liked');
                }, 1600);
            } catch (error) {
                flash('Copy failed — select the address bar instead.', 'error');
            }
        });
    });

    /* -------------------------------------------------------- image preview */
    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const target = document.querySelector(input.dataset.preview);
            if (!target || !input.files || !input.files[0]) return;
            const file = input.files[0];
            if (!file.type.startsWith('image/')) return;
            const url = URL.createObjectURL(file);
            if (target.tagName === 'IMG') {
                target.src = url;
            } else {
                target.innerHTML = '<img src="' + url + '" alt="Selected image preview">';
            }
        });
    });

    /* ------------------------------------------------------------- auto slug */
    document.querySelectorAll('[data-slug-source]').forEach((source) => {
        const target = document.querySelector(source.dataset.slugSource);
        if (!target) return;
        let locked = false;
        if (source.dataset.slugLock === '1') locked = true;
        target.addEventListener('input', () => { locked = target.value.trim() !== ''; });
        source.addEventListener('input', () => {
            if (locked) return;
            target.value = source.value
                .toLowerCase()
                .normalize('NFKD')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        });
    });

    /* -------------------------------------------------------- char counters */
    document.querySelectorAll('[data-counter-for]').forEach((counter) => {
        const input = document.querySelector(counter.dataset.counterFor);
        if (!input) return;
        const limit = parseInt(input.getAttribute('maxlength') || '0', 10);
        const update = () => {
            counter.textContent = limit
                ? input.value.length + ' / ' + limit
                : String(input.value.length);
        };
        input.addEventListener('input', update);
        update();
    });

    /* ------------------------------------------------------------- tooltips */
    if (window.bootstrap && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
    }

    /* -------------------------------------------------- newsletter unsub ? */
    document.querySelectorAll('[data-scroll-to]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const target = document.querySelector(link.dataset.scrollTo);
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
})();
