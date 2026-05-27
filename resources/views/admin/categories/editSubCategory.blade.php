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
                                            <h5>{{ $title }}</h5>
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
                                        <form action="{{ route('admin.update-sub-category') }}" method="POST" enctype="multipart/form-data" id="updateSubCategory">
                                            @csrf
                                            <input type="hidden" name="sub_category_id" value="{{ $subCategory->sub_category_id }}">
                                            <div class="theme-form theme-form-2 mega-form">
                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Category Name</label>
                                                    <div class="col-sm-9">
                                                       <select class="form-select" id="category_id" name="category_id">
                                                            <option value="">Select Category</option>
                                                            @foreach($categoryList as $category)
                                                                <option value="{{ $category->category_id }}" {{ $subCategory->category_id == $category->category_id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <p class="errors" id="err_category_id"></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Sub Category Name</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" id="sub_category_name" type="text" name="sub_category_name" value="{{ old('sub_category_name', $subCategory->sub_cat_name) }}">
                                                        <p class="errors" id="err_sub_category_name"></p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-top mt-5">
                                                    <label class="col-sm-3 col-form-label form-label-title">Sub Category Image</label>
                                                    <div class="form-group col-sm-9">
                                                        <div class="input-group">
                                                            <input type="file" class="form-control" id="sub_category_image" name="sub_category_image" accept="image/*">
                                                        </div>
                                                        <small class="text-danger" id="image-warning">Upload image only (jpeg, png, jpg, gif, svg).</small>
                                                        </div>

                                                        @if($subCategory->sub_cat_image)
                                                        <div class="mt-3">
                                                            <img src="{{ asset('public/uploads/sub_categories/' . $subCategory->sub_cat_image) }}" alt="Sub Category Image" width="150">
                                                        </div>
                                                        @endif
                                                        
                                                    </div>
                                                </div>
                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="col-sm-3 col-form-label form-label-title">Sub Category Description</label>
                                                    <div class="form-group col-sm-9">
                                                        <div class="input-group">
                                                            <textarea class="form-control" id="sub_category_description" name="sub_category_description" placeholder="Sub Category Description">{{ $subCategory->sub_cat_description }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="form-label-title col-sm-3 mb-0"></label>
                                                    <div class="col-sm-9 d-flex gap-2">
                                                        <input type="hidden" name="sub_category_id" value="{{ $subCategory->sub_category_id }}">

                                                        <button type="button" class="btn btn-solid" onclick="updateSubCategory()">Update Sub Category</button>

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