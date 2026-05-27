@extends('layouts.app')

@section('content')

    <style>
        select,
        .col-12.col-md-4 input[type="text"],
        .flex-fill {
            height: 38px !important;
        }
    </style>

    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title-header option-title d-flex align-items-center mb-4">
                        <h5><i class="ri-star-line me-2"></i>Product Reviews</h5>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Stats --}}
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-3">
                            <div class="card border-0 h-100" style="background:#e8f5e9;">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted">Total Reviews</small>
                                        <h3 class="mb-0">{{ $totalCount }}</h3>
                                    </div>
                                    <i class="ri-chat-3-line fs-2 text-success opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card border-0 h-100" style="background:#fff8e1;">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted">Pending</small>
                                        <h3 class="mb-0">{{ $pendingCount }}</h3>
                                    </div>
                                    <i class="ri-time-line fs-2 text-warning opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card border-0 h-100" style="background:#e3f2fd;">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted">Approved</small>
                                        <h3 class="mb-0">{{ $approvedCount }}</h3>
                                    </div>
                                    <i class="ri-checkbox-circle-line fs-2 text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="card border-0 h-100" style="background:#fce4ec;">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted">Rejected</small>
                                        <h3 class="mb-0">{{ $rejectedCount }}</h3>
                                    </div>
                                    <i class="ri-close-circle-line fs-2 text-danger opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filter toolbar --}}
                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('admin.product-reviews') }}"
                                class="row g-2 align-items-end">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Search</label>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        placeholder="Product name or review text" value="{{ request('search') }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small mb-1">Status</label>
                                    <select name="status" class="form-select form-select">
                                        <option value="">All Status</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Approved</option>
                                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <label class="form-label small mb-1">Rating</label>
                                    <select name="rating" class="form-select form-select">
                                        <option value="">All Ratings</option>
                                        @for($r = 5; $r >= 1; $r--)
                                            <option value="{{ $r }}" {{ request('rating') == $r ? 'selected' : '' }}>{{ $r }} Star
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-theme  flex-fill">Apply</button>
                                    <a href="{{ route('admin.product-reviews') }}"
                                        class="btn btn-outline-secondary flex-fill">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="card card-table">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-modern align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Customer</th>
                                            <th>Rating</th>
                                            <th>Review</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-center text-nowrap">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reviews as $review)
                                            <tr>
                                                <td>{{ $review->review_id }}</td>
                                                <td>
                                                    @if($review->product)
                                                        <span class="fw-medium">{{ $review->product->name }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($review->customer && $review->customer->user)
                                                        {{ $review->customer->user->name ?? '—' }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    <span class="text-warning">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="ri-star-{{ $i <= $review->rating ? 'fill' : 'line' }}"></i>
                                                        @endfor
                                                    </span>
                                                    <small class="ms-1 text-muted">({{ $review->rating }})</small>
                                                </td>
                                                <td style="max-width:250px;">
                                                    <span class="w-100" title="{{ $review->review }}">
                                                        {{ \Illuminate\Support\Str::limit($review->review, 80) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($review->status == 1)
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($review->status == 2)
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    {{ $review->created_at ? $review->created_at->format('d M Y') : '—' }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2  review-actions">
                                                        @if($review->status != 1)
                                                            <form method="POST"
                                                                action="{{ route('admin.product-reviews.update-status', $review->review_id) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="1">
                                                                <button type="submit" class="btn p-0"
                                                                    title="Approve">
                                                                    <i class="ri-check-line text-success" style="font-size:18px"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        @if($review->status != 2)
                                                            <form method="POST"
                                                                action="{{ route('admin.product-reviews.update-status', $review->review_id) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="2">
                                                                <button type="submit" class="btn p-0" style="" title="Reject">
                                                                    <i class="ri-close-line text-danger" style="font-size:18px"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        <form method="POST"
                                                            action="{{ route('admin.product-reviews.delete', $review->review_id) }}"
                                                            class="d-inline" onsubmit="return confirm('Delete this review?')">
                                                            @csrf
                                                            <button type="submit" class="btn p-0" style="" title="Delete">
                                                                <i class="ri-delete-bin-line text-danger"
                                                                    style="font-size:18px"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">No reviews found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($reviews->hasPages())
                            <div class="card-footer d-flex justify-content-end">
                                {{ $reviews->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection