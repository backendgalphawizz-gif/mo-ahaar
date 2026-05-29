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
                                        <h5>{{ $title ?? 'Sub Categories' }}</h5>
                                        <div class="d-flex align-items-center flex-wrap gap-2 ">
                                            <form method="GET" action="{{ route('admin.sub-category') }}" class="d-flex align-items-center justify-content-between flex-wrap w-100" style="gap: 8px;">
                                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search sub-category..." value="{{ request('search') }}" style="width: 180px;">
                                                <button type="submit" class="btn btn-outline-primary" style="height: 40px;">Search</button>
                                                <a href="{{ route('admin.add-sub-category') }}"
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
                                            <?php 
                                                // echo '<pre>';
                                                // print_r($allCategories);
                                                // echo '</pre>';
                                            // die;
                                            
                                            ?>




                                            <table class="table all-package table-modern" id="table_id">
                                                <thead>
                                                    <tr>
                                                        <th>S. No.</th>
                                                        <th>Category Image</th>
                                                        <th>Category Name</th>
                                                        <th>Sub Category Name</th>
                                                        <th>Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody>

                                                    @forelse($allSubCategories as $category)

                                                    <tr>
                                                        <td>{{ $allSubCategories->firstItem() + $loop->index }}</td>
                                                         <td>
                                                            <div class="table-image">
                                                                @if($category->sub_cat_image)
                                                                <img src="{{ asset('public/uploads/sub_categories/' . $category->sub_cat_image) }}" class="img-fluid" alt="">
                                                                @else               
                                                                <img src="{{ asset('public/assets/images/product/sample-cat.png') }}" class="img-fluid" alt="">
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>{{ $category->category_name }}</td>
                                                        <td>{{ @$category->sub_cat_name }}</td>
                                                        <td>{{ @$category->created_at->format('d-m-Y') }}</td>
                                                        <td>
                                                            <ul>
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                       data-bs-toggle="modal"
                                                                       data-bs-target="#viewSubCategoryModal"
                                                                       data-category="{{ $category->category_name }}"
                                                                       data-name="{{ $category->sub_cat_name }}"
                                                                       data-date="{{ optional($category->created_at)->format('d-m-Y') }}"
                                                                       data-image="{{ $category->sub_cat_image ? asset('public/uploads/sub_categories/' . $category->sub_cat_image) : asset('public/assets/images/product/sample-cat.png') }}">
                                                                        <i class="ri-eye-line"></i>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="{{ route('admin.edit-sub-category', Crypt::encrypt($category->sub_category_id)) }}">
                                                                        <i class="ri-pencil-line"></i>
                                                                    </a>
                                                                </li>

                                                                <li>
                                                                    <a href="javascript:void(0)" class="delete-sub-category-btn text-danger" data-form-id="delete-sub-form-{{ $category->sub_category_id }}" data-name="{{ $category->sub_category_name }}">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </a>
                                                                    <form id="delete-sub-form-{{ $category->sub_category_id }}" method="POST" action="{{ route('admin.deleteSubCategory', $category->sub_category_id) }}" class="d-none">
                                                                        @csrf
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">No sub categories found.</td>
                                                    </tr>
                                                @endforelse
                                                   
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    @if($allSubCategories->hasPages())
                                        <div class="list-pagination-wrap d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                            <p class="text-muted mb-0 small">
                                                Showing {{ $allSubCategories->firstItem() }} to {{ $allSubCategories->lastItem() }} of {{ $allSubCategories->total() }} sub categories
                                            </p>
                                            <div class="list-pagination">
                                                {{ $allSubCategories->onEachSide(1)->links('pagination::bootstrap-5') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- All User Table Ends-->

                <div class="container-fluid">
                    <!-- footer start-->
                    <footer class="footer">
                        <div class="row">
                            <div class="col-md-12 footer-copyright text-center">
                                <p class="mb-0">Copyright 2022 © Fastkart theme by pixelstrap</p>
                            </div>
                        </div>
                    </footer>
                    <!-- footer end-->
                </div>
            </div>
            <!-- Container-fluid end -->
        </div>
        <!-- Page Body End -->

        <!-- Modal Start -->
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
            <div class="modal-dialog  modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5 class="modal-title" id="staticBackdropLabel">Logging Out</h5>
                        <p>Are you sure you want to log out?</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="button-box">
                            <button type="button" class="btn btn--no" data-bs-dismiss="modal">No</button>
                            <button type="button" class="btn  btn--yes btn-primary">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal End -->
    </div>

    <div class="modal fade" id="viewSubCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Sub Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4 text-center">
                            <img id="subCategoryViewImage" src="" alt="Sub Category" class="img-fluid rounded" style="max-height:180px;object-fit:cover;border:1px solid #e5e7eb;">
                        </div>
                        <div class="col-md-8">
                            <div class="mb-2"><strong>Category:</strong> <span id="subCategoryParent">-</span></div>
                            <div class="mb-2"><strong>Name:</strong> <span id="subCategoryViewName">-</span></div>
                            <div class="mb-2"><strong>Created:</strong> <span id="subCategoryViewDate">-</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .list-pagination .pagination {
            margin-bottom: 0;
            gap: 6px;
            flex-wrap: wrap;
        }

        .list-pagination .page-item .page-link {
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

        .list-pagination .page-item.active .page-link {
            background: #0da487;
            border-color: #0da487;
            color: #fff;
        }

        .list-pagination .page-item .page-link:hover {
            background: #f0fdfa;
            border-color: #0da487;
            color: #0f766e;
        }

        .list-pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        @media (max-width: 767px) {
            .list-pagination-wrap {
                align-items: flex-start !important;
            }

            .list-pagination .page-item .page-link {
                min-width: 34px;
                height: 34px;
                font-size: 12px;
            }
        }
    </style>

    <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.delete-sub-category-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var formId = this.getAttribute('data-form-id');
                        var name = this.getAttribute('data-name') || 'this sub-category';
                        var form = document.getElementById(formId);
                        if (!form) return;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Delete sub-category?',
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

                var modal = document.getElementById('viewSubCategoryModal');
                if (!modal) return;

                modal.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    if (!trigger) return;

                    document.getElementById('subCategoryParent').textContent = trigger.getAttribute('data-category') || '-';
                    document.getElementById('subCategoryViewName').textContent = trigger.getAttribute('data-name') || '-';
                    document.getElementById('subCategoryViewDate').textContent = trigger.getAttribute('data-date') || '-';
                    document.getElementById('subCategoryViewImage').src = trigger.getAttribute('data-image');
                });
            });
    </script>

@endsection