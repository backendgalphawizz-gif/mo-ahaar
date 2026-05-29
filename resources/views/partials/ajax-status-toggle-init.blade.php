<script>
(function () {
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function isSuccess(data) {
        return data && (data.success === true || data.status === true);
    }

    function isActiveValue(value) {
        return value === true || value === 1 || value === '1';
    }

    function updateRelatedUi(el, data) {
        if (typeof data.is_active !== 'undefined') {
            el.checked = isActiveValue(data.is_active);
        }

        var pillSel = el.getAttribute('data-status-pill');
        if (pillSel) {
            var pill = document.querySelector(pillSel);
            if (pill) {
                var active = isActiveValue(data.is_active);
                var label = data.label || (active ? 'Active' : 'Inactive');
                pill.textContent = label;
                pill.classList.remove('active', 'inactive');
                pill.classList.add(active ? 'active' : 'inactive');
            }
        }

        var labelSel = el.getAttribute('data-status-label');
        if (labelSel) {
            var statusLabel = document.querySelector(labelSel);
            if (statusLabel) {
                var activeLabel = isActiveValue(data.is_active);
                statusLabel.textContent = activeLabel ? 'ACTIVE' : 'INACTIVE';
                statusLabel.classList.remove('on', 'off');
                statusLabel.classList.add(activeLabel ? 'on' : 'off');
            }
        }
    }

    window.bindAjaxStatusToggles = function (root) {
        (root || document).querySelectorAll('.ajax-status-toggle:not([data-toggle-bound])').forEach(function (el) {
            el.setAttribute('data-toggle-bound', '1');
            el.addEventListener('change', function () {
                var url = el.getAttribute('data-toggle-url');
                if (!url) {
                    return;
                }

                var previousChecked = !el.checked;
                el.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (r) {
                        return r.json().then(function (data) {
                            return { ok: r.ok, data: data };
                        });
                    })
                    .then(function (res) {
                        if (!res.ok || !isSuccess(res.data)) {
                            el.checked = previousChecked;
                            var msg = (res.data && res.data.message) ? res.data.message : 'Could not update status.';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'error', title: 'Error', text: msg, timer: 2500, showConfirmButton: false });
                            } else {
                                alert(msg);
                            }
                            return;
                        }
                        updateRelatedUi(el, res.data);
                    })
                    .catch(function () {
                        el.checked = previousChecked;
                        var errMsg = 'Could not update status. Please try again.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error', text: errMsg, timer: 2500, showConfirmButton: false });
                        } else {
                            alert(errMsg);
                        }
                    })
                    .finally(function () {
                        el.disabled = false;
                    });
            });
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.bindAjaxStatusToggles(document);
    });
})();
</script>
