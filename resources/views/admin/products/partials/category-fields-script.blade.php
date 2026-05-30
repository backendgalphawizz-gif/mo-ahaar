<script>
document.addEventListener('DOMContentLoaded', function () {
    var categorySelect = document.getElementById('category_id');
    var subCategorySelect = document.getElementById('sub_category_id');
    if (!categorySelect || !subCategorySelect) {
        return;
    }

    var subCategoryBaseUrl = @json(url('/get-sub-categories'));
    var initialCategoryId = categorySelect.value;
    var initialSubCategoryId = @json((string) old('sub_category_id', $selectedSubCategoryId ?? ''));

    function setSubCategoryOptions(items, selectedId) {
        subCategorySelect.innerHTML = '<option value="">Select sub category (optional)</option>';
        (items || []).forEach(function (item) {
            var option = document.createElement('option');
            option.value = item.sub_category_id;
            option.textContent = item.sub_cat_name;
            if (String(selectedId) === String(item.sub_category_id)) {
                option.selected = true;
            }
            subCategorySelect.appendChild(option);
        });
    }

    function loadSubCategories(categoryId, selectedId, forceReload) {
        if (!categoryId) {
            subCategorySelect.disabled = true;
            setSubCategoryOptions([], '');
            return;
        }

        subCategorySelect.disabled = false;

        if (!forceReload && String(categoryId) === String(initialCategoryId) && subCategorySelect.options.length > 1) {
            if (selectedId) {
                subCategorySelect.value = String(selectedId);
            }
            return;
        }

        fetch(subCategoryBaseUrl + '/' + encodeURIComponent(categoryId), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) { return response.json(); })
            .then(function (items) {
                setSubCategoryOptions(items, selectedId || '');
            })
            .catch(function () {
                setSubCategoryOptions([], '');
            });
    }

    categorySelect.addEventListener('change', function () {
        loadSubCategories(categorySelect.value, '', true);
    });

    if (categorySelect.value) {
        loadSubCategories(categorySelect.value, initialSubCategoryId, false);
    }
});
</script>
