@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h5 class="mb-0">Static Pages</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $activeAudience = $selectedAudience ?? 'user';
            $activePages = $pagesByAudience[$activeAudience] ?? [];
            $privacyPage = $activePages['privacy-policy'] ?? null;
            $termsPage = $activePages['terms-and-conditions'] ?? null;
            $faqPage = $activePages['faqs'] ?? null;
        @endphp

        <div class="card dashboard-card">
            <div class="card-body">
                <h6 class="mb-3"><i class="ri-file-list-3-line me-1"></i>Static Page Management</h6>

                <div class="audience-switch mb-3">
                    <button type="button" class="audience-btn {{ $activeAudience === 'user' ? 'active' : '' }}" data-audience="user">User App</button>
                    <button type="button" class="audience-btn {{ $activeAudience === 'driver' ? 'active' : '' }}" data-audience="driver">Driver App</button>
                </div>

                <div class="page-tabs mb-2">
                    <button type="button" class="page-tab-btn active" data-tab="privacy">Privacy Policy</button>
                    <button type="button" class="page-tab-btn" data-tab="terms">Terms & Conditions</button>
                    <button type="button" class="page-tab-btn" data-tab="faq">FAQs</button>
                </div>

                <div class="tab-content-wrap">
                    <div class="tab-panel active" data-panel="privacy">
                        <form method="POST" action="{{ route('admin.static-pages.save') }}" class="row g-2">
                            @csrf
                            <input type="hidden" name="audience" class="audience-field" value="{{ $activeAudience }}">
                            <input type="hidden" name="page_type" value="privacy-policy">
                            <input type="hidden" name="title" value="Privacy Policy">
                            <input type="hidden" name="status" value="{{ (int) ($privacyPage?->status ?? 1) }}">
                            <div class="col-12">
                                <label class="form-label">Privacy Policy Content</label>
                                <textarea name="content" rows="4" class="form-control" placeholder="Privacy Policy Content">{{ $privacyPage?->content }}</textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-theme"><i class="ri-save-line me-1"></i>Save Privacy Policy</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-panel" data-panel="terms">
                        <form method="POST" action="{{ route('admin.static-pages.save') }}" class="row g-2">
                            @csrf
                            <input type="hidden" name="audience" class="audience-field" value="{{ $activeAudience }}">
                            <input type="hidden" name="page_type" value="terms-and-conditions">
                            <input type="hidden" name="title" value="Terms & Conditions">
                            <input type="hidden" name="status" value="{{ (int) ($termsPage?->status ?? 1) }}">
                            <div class="col-12">
                                <label class="form-label">Terms & Conditions Content</label>
                                <textarea name="content" rows="4" class="form-control" placeholder="Terms & Conditions Content">{{ $termsPage?->content }}</textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-theme"><i class="ri-save-line me-1"></i>Save Terms & Conditions</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-panel" data-panel="faq">
                        <form method="POST" action="{{ route('admin.static-pages.save') }}" id="faqForm">
                            @csrf
                            <input type="hidden" name="audience" class="audience-field" value="{{ $activeAudience }}">
                            <input type="hidden" name="page_type" value="faqs">
                            <input type="hidden" name="title" value="FAQs">
                            <input type="hidden" name="status" value="{{ (int) ($faqPage?->status ?? 1) }}">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Manage FAQs</label>
                                <button type="button" class="btn btn-theme btn-sm" id="addFaqRow">Add New FAQ</button>
                            </div>

                            <div id="faqRows"></div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-theme"><i class="ri-save-line me-1"></i>Save FAQs</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const audienceButtons = document.querySelectorAll('.audience-btn');
    const audienceFields = document.querySelectorAll('.audience-field');
    const selectedAudience = "{{ $activeAudience }}";
    const pagesByAudience = @json($pagesByAudience);
    const tabButtons = document.querySelectorAll('.page-tab-btn');
    const panels = document.querySelectorAll('.tab-panel');
    const faqRows = document.getElementById('faqRows');
    const addFaqRow = document.getElementById('addFaqRow');

    function decodeHtmlEntities(input) {
        const txt = document.createElement('textarea');
        txt.innerHTML = input;
        return txt.value;
    }

    function parseFaqItems(html) {
        if (!html) return [];
        const blockRegex = /<p><strong>(.*?)<\/strong><br\s*\/?>(.*?)<\/p>/gi;
        const items = [];
        let match = null;
        while ((match = blockRegex.exec(html)) !== null) {
            items.push({
                question: decodeHtmlEntities(match[1].replace(/<[^>]*>/g, '').trim()),
                answer: decodeHtmlEntities(match[2].replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]*>/g, '').trim())
            });
        }
        return items;
    }

    function faqRowTemplate(index, item) {
        const q = item && item.question ? item.question : '';
        const a = item && item.answer ? item.answer : '';
        return `
            <div class="faq-row border rounded p-3 mb-3">
                <label class="form-label">Question</label>
                <input type="text" name="faq_items[${index}][question]" class="form-control mb-2" value="${q.replace(/"/g, '&quot;')}" placeholder="Enter question">
                <label class="form-label">Answer</label>
                <textarea name="faq_items[${index}][answer]" rows="2" class="form-control mb-2" placeholder="Enter answer">${a}</textarea>
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-danger remove-faq-row">Delete</button>
                </div>
            </div>
        `;
    }

    function renderFaq(audience) {
        faqRows.innerHTML = '';
        const faqHtml = (((pagesByAudience || {})[audience] || {})['faqs'] || {}).content || '';
        const items = parseFaqItems(faqHtml);
        if (items.length === 0) {
            faqRows.insertAdjacentHTML('beforeend', faqRowTemplate(0, null));
            return;
        }
        items.forEach(function (item, index) {
            faqRows.insertAdjacentHTML('beforeend', faqRowTemplate(index, item));
        });
    }

    function normalizeFaqIndexes() {
        faqRows.querySelectorAll('.faq-row').forEach(function (row, index) {
            const q = row.querySelector('input[name*="[question]"]');
            const a = row.querySelector('textarea[name*="[answer]"]');
            if (q) q.name = `faq_items[${index}][question]`;
            if (a) a.name = `faq_items[${index}][answer]`;
        });
    }

    function setAudience(audience) {
        audienceButtons.forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-audience') === audience);
        });
        audienceFields.forEach(function (field) {
            field.value = audience;
        });
        renderFaq(audience);
    }

    audienceButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setAudience(btn.getAttribute('data-audience'));
        });
    });

    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tab = btn.getAttribute('data-tab');
            tabButtons.forEach(function (x) { x.classList.remove('active'); });
            panels.forEach(function (x) { x.classList.remove('active'); });
            btn.classList.add('active');
            const panel = document.querySelector(`.tab-panel[data-panel="${tab}"]`);
            if (panel) panel.classList.add('active');
        });
    });

    if (addFaqRow) {
        addFaqRow.addEventListener('click', function () {
            const index = faqRows.querySelectorAll('.faq-row').length;
            faqRows.insertAdjacentHTML('beforeend', faqRowTemplate(index, null));
        });
    }

    faqRows.addEventListener('click', function (event) {
        if (!event.target.classList.contains('remove-faq-row')) return;
        const row = event.target.closest('.faq-row');
        if (row) row.remove();
        if (!faqRows.querySelector('.faq-row')) {
            faqRows.insertAdjacentHTML('beforeend', faqRowTemplate(0, null));
        }
        normalizeFaqIndexes();
    });

    setAudience(selectedAudience || 'user');
});
</script>
<style>
.audience-switch, .page-tabs { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; background: #f1f5f9; border-radius: 999px; padding: 3px; }
.page-tabs { grid-template-columns: 1fr 1fr 1fr; }
.audience-btn, .page-tab-btn { border: 0; background: transparent; border-radius: 999px; padding: 8px 10px; font-size: 13px; }
.audience-btn.active, .page-tab-btn.active { background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .08); font-weight: 600; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }
</style>
@endsection