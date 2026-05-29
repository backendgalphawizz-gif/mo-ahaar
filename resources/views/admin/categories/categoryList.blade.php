@extends('layouts.app')

@section('content')


<div class="page-wrapper compact-wrapper" id="pageWrapper">
        
        <!-- Page Body Start -->
        <div class="page-body-wrapper">
            
           
            <div class="page-body">
                <!-- All User Table Start -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card card-table">
                                <div class="card-body">
                                    <div class="title-header option-title d-flex align-items-center justify-content-between">
                                        <h5>All Category</h5>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <form method="GET" action="{{ route('admin.categories') }}" class="d-flex align-items-center justify-content-between flex-wrap w-100" style="gap: 8px;">
                                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search category..." value="{{ request('search') }}" style="width: 180px;">
                                                <button type="submit" class="btn btn-outline-primary" style="height: 40x;">Search</button>
                                                <a href="{{ route('admin.add-category') }}"
                                                class="align-items-center btn btn-theme d-flex ">
                                                <i data-feather="plus-square"></i>Add New
                                            </a>
                                            </form>
                                            
                                        </div>
                                    </div>
                                    @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                    @endif
                                    <div class="table-responsive category-table">
                                        <div>
                                            <table class="table all-package table-modern" id="table_id">
                                                <thead>
                                                    <tr>
                                                        <th>S. No.</th>
                                                        <th>Category Image</th>
                                                        <th>Category Name</th>
                                                        <th>Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($allCategories as $category)
                                                    <tr>
                                                        <td>{{ $allCategories->firstItem() + $loop->index }}</td>
                                                         <td>
                                                            <div class="table-image">
                                                                @if($category->category_image)
                                                                <img src="{{ asset('public/uploads/categories/' . $category->category_image) }}" class="img-fluid" alt="">
                                                                @else               
                                                                <img src="{{ asset('public/assets/images/product/sample-cat.png') }}" class="img-fluid" alt="">
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>{{ $category->category_name }}</td>
                                                        <td>{{ $category->created_at->format('d-m-Y') }}</td>
                                                        <td>
                                                            <ul>
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                       data-bs-toggle="modal"
                                                                       data-bs-target="#viewCategoryModal"
                                                                       data-name="{{ $category->category_name }}"
                                                                       data-date="{{ optional($category->created_at)->format('d-m-Y') }}"
                                                                       data-image="{{ $category->category_image ? asset('public/uploads/categories/' . $category->category_image) : asset('public/assets/images/product/sample-cat.png') }}">
                                                                        <i class="ri-eye-line"></i>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="{{ route('admin.edit-category', Crypt::encrypt($category->category_id)) }}">
                                                                        <i class="ri-pencil-line"></i>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="javascript:void(0)" class="delete-category-btn text-danger" data-form-id="delete-category-form-{{ $category->category_id }}" data-name="{{ $category->category_name }}">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </a>
                                                                    <form id="delete-category-form-{{ $category->category_id }}" method="POST" action="{{ route('admin.deleteCategory', $category->category_id) }}" class="d-none">
                                                                        @csrf
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">No categories found.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    @if($allCategories->hasPages())
                                        <div class="category-pagination-wrap d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                            <p class="text-muted mb-0 small">
                                                Showing {{ $allCategories->firstItem() }} to {{ $allCategories->lastItem() }} of {{ $allCategories->total() }} categories
                                            </p>
                                            <div class="category-pagination">
                                                {{ $allCategories->onEachSide(1)->links('pagination::bootstrap-5') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4 text-center">
                            <img id="categoryViewImage" src="" alt="Category" class="img-fluid rounded" style="max-height:180px;object-fit:cover;border:1px solid #e5e7eb;">
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2"><strong>Name:</strong> <span id="categoryViewName">-</span></div>
                            <div class="mb-2"><strong>Created:</strong> <span id="categoryViewDate">-</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .category-pagination .pagination {
            margin-bottom: 0;
            gap: 6px;
            flex-wrap: wrap;
        }

        .category-pagination .page-item .page-link {
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            color: #334155;
            min-width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 600;
            background: #fff;
            box-shadow: none;
        }

        .category-pagination .page-item.active .page-link {
            background: #0da487;
            border-color: #0da487;
            color: #fff;
        }

        .category-pagination .page-item .page-link:hover {
            background: #f0fdfa;
            border-color: #0da487;
            color: #0f766e;
        }

        .category-pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        @media (max-width: 767px) {
            .category-pagination-wrap {
                align-items: flex-start !important;
            }

            .category-pagination .page-item .page-link {
                min-width: 34px;
                height: 34px;
                font-size: 12px;
            }
        }
    </style>

    <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.delete-category-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var formId = this.getAttribute('data-form-id');
                        var name = this.getAttribute('data-name') || 'this category';
                        var form = document.getElementById(formId);
                        if (!form) return;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Delete category?',
                                text: 'Delete "' + name + '"?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, delete',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#dc3545'
                            }).then(function (result) {
                                if (result.isConfirmed) form.submit();
                            });
                        } else if (confirm('Delete "' + name + '"?')) {
                            form.submit();
                        }
                    });
                });

                var modal = document.getElementById('viewCategoryModal');
                if (!modal) return;

                modal.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    if (!trigger) return;

                    document.getElementById('categoryViewName').textContent = trigger.getAttribute('data-name') || '-';
                    document.getElementById('categoryViewDate').textContent = trigger.getAttribute('data-date') || '-';
                    document.getElementById('categoryViewImage').src = trigger.getAttribute('data-image');
                });
            });
    </script>

@endsection