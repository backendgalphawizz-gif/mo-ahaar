<style>
.password-toggle-wrap {
    position: relative;
    display: block;
    width: 100%;
}
.password-toggle-wrap > .form-control,
.password-toggle-wrap > input[type="password"],
.password-toggle-wrap > input[type="text"] {
    padding-right: 42px !important;
    width: 100%;
}
.password-toggle-btn {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: transparent;
    color: #6b7280;
    padding: 4px 8px;
    line-height: 1;
    cursor: pointer;
    z-index: 5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    border-radius: 4px;
}
.password-toggle-btn:hover { color: #111827; }
.password-toggle-btn:focus { outline: none; box-shadow: none; }
.password-toggle-btn i { pointer-events: none; }
.admin-panel .password-toggle-btn { color: #9ca3af; }
.admin-panel .password-toggle-btn:hover { color: var(--moa-red, #ed1c24); }
</style>
<script>
(function () {
    'use strict';

    function hasExistingToggle(input) {
        if (input.dataset.passwordToggleInit === '1') {
            return true;
        }
        var wrap = input.closest('.password-toggle-wrap, .password-input-wrapper');
        if (wrap && wrap.querySelector('.password-toggle-btn, .password-toggle-icon, [data-password-toggle]')) {
            return true;
        }
        var group = input.closest('.input-group');
        if (group) {
            var btn = group.querySelector('button[type="button"]');
            if (btn && btn.querySelector('[class*="ri-eye"]')) {
                return true;
            }
        }
        return false;
    }

    function bindToggleButton(btn, input) {
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show
                ? '<i class="ri-eye-off-line" aria-hidden="true"></i>'
                : '<i class="ri-eye-line" aria-hidden="true"></i>';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    }

    function enhancePasswordInput(input) {
        if (!input || input.type !== 'password' || hasExistingToggle(input)) {
            return;
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'password-toggle-wrap';

        var parent = input.parentNode;
        if (!parent) {
            return;
        }

        parent.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'password-toggle-btn';
        btn.setAttribute('aria-label', 'Show password');
        btn.innerHTML = '<i class="ri-eye-line" aria-hidden="true"></i>';
        wrapper.appendChild(btn);

        bindToggleButton(btn, input);
        input.dataset.passwordToggleInit = '1';
    }

    function initPasswordToggles(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var inputs = scope === document
            ? document.querySelectorAll('input[type="password"]')
            : scope.querySelectorAll('input[type="password"]');

        inputs.forEach(enhancePasswordInput);
    }

    window.initPasswordToggles = initPasswordToggles;

    document.addEventListener('DOMContentLoaded', function () {
        initPasswordToggles();

        if (typeof MutationObserver !== 'undefined') {
            var scheduled = false;
            var observer = new MutationObserver(function () {
                if (scheduled) {
                    return;
                }
                scheduled = true;
                requestAnimationFrame(function () {
                    scheduled = false;
                    initPasswordToggles();
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    });
})();
</script>
