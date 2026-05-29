<script>
document.addEventListener('DOMContentLoaded', function () {
    var priceInput = document.getElementById('product_price');
    var gstSelect = document.getElementById('gst_tax_id');
    var typeSelect = document.getElementById('gst_calculation_type');
    var previewText = document.getElementById('gst_preview_text');
    var typeHelp = document.getElementById('gst_type_help');
    var GST_EXCLUDED = @json(\App\Models\Product::GST_EXCLUDED);
    var GST_INCLUDED = @json(\App\Models\Product::GST_INCLUDED);

    function getRate() {
        if (!gstSelect || !gstSelect.value) {
            return 0;
        }
        var opt = gstSelect.options[gstSelect.selectedIndex];
        return parseFloat(opt.getAttribute('data-percentage') || '0') || 0;
    }

    function formatMoney(n) {
        return '₹' + n.toFixed(2);
    }

    function updateGstPreview() {
        var price = parseFloat(priceInput ? priceInput.value : '0') || 0;
        var rate = getRate();
        var type = typeSelect ? typeSelect.value : GST_EXCLUDED;

        if (typeHelp) {
            typeHelp.textContent = type === GST_INCLUDED
                ? 'Selling price already includes GST; base amount is calculated backwards.'
                : 'Customer pays base price plus GST on top.';
        }

        if (!previewText) {
            return;
        }

        if (price <= 0) {
            previewText.textContent = 'Enter price and select GST to see breakdown.';
            previewText.className = 'text-muted';
            return;
        }

        if (rate <= 0) {
            previewText.textContent = 'No GST applied. Customer pays ' + formatMoney(price) + '.';
            previewText.className = 'text-muted';
            return;
        }

        var base, gst, total;
        if (type === GST_INCLUDED) {
            base = rate > 0 ? price / (1 + rate / 100) : price;
            gst = price - base;
            total = price;
        } else {
            base = price;
            gst = price * rate / 100;
            total = base + gst;
        }

        previewText.innerHTML = 'Base: <strong>' + formatMoney(base) + '</strong> + GST (' + rate + '%): <strong>' + formatMoney(gst) + '</strong> = Customer pays <strong>' + formatMoney(total) + '</strong>';
        previewText.className = '';
    }

    if (priceInput) priceInput.addEventListener('input', updateGstPreview);
    if (gstSelect) gstSelect.addEventListener('change', updateGstPreview);
    if (typeSelect) typeSelect.addEventListener('change', updateGstPreview);
    updateGstPreview();
});
</script>
