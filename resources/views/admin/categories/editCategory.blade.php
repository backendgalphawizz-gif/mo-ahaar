@extends('layouts.app')

@section('content')


<div class="page-wrapper compact-wrapper" id="pageWrapper">

    <!-- Page Body start -->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->

        <!-- Page Sidebar Ends-->

        <div class="page-body">

            <!-- New Product Add Start -->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-8 m-auto">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-header-2">
                                            <h5>Category Information</h5>
                                        </div>
                                        @if(session('success'))
                                            <div class="alert alert-success">{{ session('success') }}</div>
                                        @endif
                                        @if(session('error'))
                                            <div class="alert alert-danger">{{ session('error') }}</div>
                                        @endif
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <form action="{{ route('admin.update-category') }}" method="POST" enctype="multipart/form-data" id="updatecategory">
                                            @csrf
                                            <input type="hidden" name="category_id" value="{{ $category->category_id }}">
                                            <div class="theme-form theme-form-2 mega-form">
                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Category Name</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" id="category_name" type="text" name="category_name" value="{{ old('category_name', $category->category_name) }}">
                                                        <p class="errors" id="err_category_name"></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-top mt-5">
                                                    <label class="col-sm-3 col-form-label form-label-title">Category Image</label>
                                                    <div class="form-group col-sm-9">
                                                        <div class="input-group">
                                                            <input type="file" class="form-control" id="category_image" name="category_image" accept="image/*">
                                                        </div>
                                                        <small class="text-danger" id="image-warning">Upload image only (jpeg, png, jpg, gif, svg).</small>

                                                        @if($category->category_image)
                                                        <div class="mt-3">
                                                            <img src="{{ asset('public/uploads/categories/' . $category->category_image) }}" alt="Category Image" width="150">
                                                        </div>
                                                        @endif
                                                        
                                                    </div>
                                                </div>
                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="col-sm-3 col-form-label form-label-title">Category Description</label>
                                                    <div class="form-group col-sm-9">
                                                        <div class="input-group">
                                                            <textarea class="form-control" id="category_description" name="category_description" placeholder="Category Description">{{ old('category_description', $category->category_description) }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="form-label-title col-sm-3 mb-0"></label>
                                                    <div class="col-sm-9 d-flex gap-2">
                                                        <input type="hidden" name="category_id" value="{{ $category->category_id }}">

                                                        <button type="button" class="btn btn-solid" onclick="updateCategory()">Update Category</button>

                                                        <button onclick="history.back()" class="btn btn-outline-secondary">   Back </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Container-fluid End -->
    </div>
    <!-- Page Body End -->
</div>


@endsection