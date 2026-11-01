/* ==========================================================================
   ObscuraX — admin panel interactions
   ========================================================================== */
(function () {
    'use strict';

    const csrf = () => (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ------------------------------------------------------ mobile sidebar */
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminBackdrop');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    const closeSidebar = () => {
        if (sidebar) sidebar.classList.remove('is-open');
        if (backdrop) backdrop.classList.remove('is-open');
    };

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('is-open');
            if (backdrop) backdrop.classList.toggle('is-open');
        });
    }
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
    });

    /* -------------------------------------------------------------- Quill */
    const editorHost = document.getElementById('blog-editor');
    const contentField = document.querySelector('input[name="content"]');
    if (editorHost && contentField) {
        if (window.Quill) {
            const uploadUrl = editorHost.dataset.uploadUrl || '';
            const quill = new Quill(editorHost, {
                theme: 'snow',
                placeholder: 'Write the investigation…',
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ color: [] }, { background: [] }],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ align: [] }],
                            ['blockquote', 'code-block'],
                            ['link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: function () {
                                const input = document.createElement('input');
                                input.type = 'file';
                                input.accept = 'image/jpeg,image/png,image/webp,image/gif';
                                input.onchange = async () => {
                                    if (!input.files || !input.files[0]) return;
                                    const form = new FormData();
                                    form.append('image', input.files[0]);
                                    try {
                                        const response = await fetch(uploadUrl, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-Token': csrf(),
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json'
                                            },
                                            body: form
                                        });
                                        const data = await response.json();
                                        if (!data.ok) {
                                            alert(data.message || 'Upload failed.');
                                            return;
                                        }
                                        const range = quill.getSelection(true);
                                        quill.insertEmbed(range.index, 'image', data.url, 'user');
                                        quill.setSelection(range.index + 1);
                                    } catch (error) {
                                        alert('Upload failed. Please try again.');
                                    }
                                };
                                input.click();
                            }
                        }
                    }
                }
            });

            if (contentField.value.trim() !== '') {
                quill.root.innerHTML = contentField.value;
            }

            const form = editorHost.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    contentField.value = quill.root.innerHTML;
                });
            }
        } else {
            const fallback = document.createElement('textarea');
            fallback.className = 'editor-fallback';
            fallback.value = contentField.value;
            fallback.addEventListener('input', () => { contentField.value = fallback.value; });
            editorHost.replaceWith(fallback);
        }
    }

    /* -------------------------------------------------------------- charts */
    const chartCanvases = document.querySelectorAll('canvas[data-chart]');
    if (chartCanvases.length && window.Chart) {
        Chart.defaults.color = '#958fa6';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.07)';
        Chart.defaults.font.family = "'Inter', -apple-system, 'Segoe UI', sans-serif";
        Chart.defaults.font.size = 11;

        chartCanvases.forEach((canvas) => {
            const source = document.getElementById(canvas.dataset.chart);
            if (!source) return;
            let spec;
            try {
                spec = JSON.parse(source.textContent);
            } catch (error) {
                return;
            }

            const radial = ['doughnut', 'pie', 'polarArea'].includes(spec.type);
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: spec.legend !== false,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 7,
                            boxHeight: 7,
                            padding: 16
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 13, 25, 0.97)',
                        borderColor: 'rgba(167,139,250,0.3)',
                        borderWidth: 1,
                        titleColor: '#f4effc',
                        bodyColor: '#c9c3d7',
                        padding: 10,
                        cornerRadius: 10,
                        displayColors: true,
                        boxWidth: 7,
                        boxHeight: 7,
                        usePointStyle: true
                    }
                }
            };

            if (!radial) {
                options.scales = {
                    x: {
                        grid: { color: 'rgba(255,255,255,0.045)', drawBorder: false },
                        ticks: { color: '#8b8499', maxRotation: 0, autoSkipPadding: 14 }
                    },
                    y: {
                        beginAtZero: spec.beginAtZero !== false,
                        grid: { color: 'rgba(255,255,255,0.045)', drawBorder: false },
                        ticks: { color: '#8b8499', precision: 0 }
                    }
                };
                if (spec.horizontal) {
                    options.indexAxis = 'y';
                }
            }

            if (radial) {
                options.cutout = spec.type === 'doughnut' ? '68%' : undefined;
                options.plugins.legend.position = 'right';
            }

            new Chart(canvas, {
                type: spec.type || 'line',
                data: { labels: spec.labels || [], datasets: spec.datasets || [] },
                options: options
            });
        });
    }

    /* --------------------------------------------------- password generator */
    document.querySelectorAll('[data-generate-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.querySelector(button.dataset.generatePassword);
            if (!input) return;
            const sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%^&*'];
            let password = '';
            sets.forEach((set) => { password += set[Math.floor(Math.random() * set.length)]; });
            const all = sets.join('');
            while (password.length < 16) {
                password += all[Math.floor(Math.random() * all.length)];
            }
            password = password.split('').sort(() => Math.random() - 0.5).join('');
            input.value = password;
            input.type = 'text';
            input.dispatchEvent(new Event('input'));
            if (navigator.clipboard) {
                navigator.clipboard.writeText(password).catch(() => {});
            }
        });
    });

    /* ------------------------------------------------------- role switcher */
    document.querySelectorAll('[data-toggle-target]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.toggleTarget);
            if (target) target.classList.toggle('d-none');
        });
    });

    /* --------------------------------------------------------- reply panels */
    document.querySelectorAll('[data-admin-reply]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = document.getElementById(button.dataset.adminReply);
            if (row) row.classList.toggle('d-none');
        });
    });

    /* ------------------------------------------------------ CSV note ??? no */
    document.querySelectorAll('[data-print]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });
})();
