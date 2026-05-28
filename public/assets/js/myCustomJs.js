$(document).ready(function () {
    if (!document.getElementById('global-image-preview-style')) {
        var styleTag = document.createElement('style');
        styleTag.id = 'global-image-preview-style';
        styleTag.textContent = '' +
            '.global-image-preview-wrap{margin-top:8px;display:inline-flex;flex-direction:column;gap:4px;}' +
            '.global-image-preview-label{font-size:11px;color:#6b7280;}' +
            '.global-image-preview{max-width:140px;max-height:140px;border:1px solid #d1d5db;border-radius:8px;object-fit:cover;background:#f9fafb;padding:2px;}';
        document.head.appendChild(styleTag);
    }


    $('.numberonly').keypress(function (e) {
        var charCode = (e.which) ? e.which : event.keyCode
        if (String.fromCharCode(charCode).match(/[^0-9]/g))
            return false;
    });

    $('.decimalInput').on('input', function(){
        var value = $(this).val();
        // Allow only digits and one decimal point
        if (!/^\d*\.?\d*$/.test(value)) {
            $(this).val(value.slice(0, -1));
        }
    });

    // Global image preview for all add/edit forms in admin/vendor panels.
    $(document).on('change', 'input[type="file"]', function () {
        var input = this;
        if (!input || !input.files || !input.files.length) {
            removePreview(input);
            return;
        }

        var file = input.files[0];
        if (!file || !file.type || file.type.indexOf('image/') !== 0) {
            removePreview(input);
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            renderPreview(input, event.target.result);
        };
        reader.readAsDataURL(file);
    });

    function renderPreview(input, imageSrc) {
        if (!input) return;

        var $input = $(input);
        var previewId = 'img-preview-' + ensureInputId($input);
        var $existing = $('#' + previewId);

        if ($existing.length === 0) {
            var $wrapper = $('<div/>', {
                id: previewId,
                class: 'global-image-preview-wrap'
            });
            var $label = $('<small/>', {
                class: 'global-image-preview-label',
                text: 'Preview'
            });
            var $img = $('<img/>', {
                class: 'global-image-preview',
                alt: 'Selected image preview'
            });
            $wrapper.append($label).append($img);
            $input.after($wrapper);
            $existing = $wrapper;
        }

        $existing.find('img.global-image-preview').attr('src', imageSrc);
    }

    function removePreview(input) {
        if (!input) return;
        var $input = $(input);
        var previewId = 'img-preview-' + ensureInputId($input);
        $('#' + previewId).remove();
    }

    function ensureInputId($input) {
        var existingId = $input.attr('id');
        if (existingId) return existingId;

        var generated = 'file-' + Math.random().toString(36).substring(2, 10);
        $input.attr('id', generated);
        return generated;
    }

});


function setvalidation(strId,strT,strMsg){
    if(strT=='S'){
        jQuery('#'+strId).css('border','');
        jQuery('#err_'+strId).html('');
    }else if(strT=='F'){
        jQuery('#'+strId).css('border','1px solid #F00');
        jQuery('#err_'+strId).html(strMsg);
    }
}



function createCategory(){

    var submircheak = 0 ;
    var category_name = jQuery('#category_name').val();
    if (category_name == null || category_name==""){
        submircheak =1;
        setvalidation('category_name','F',"Please Enter Category title");
    }else{
         setvalidation('category_name','S','');
    }
   
    if(submircheak ==1){
       return false;
    }else{
        $('#categoryForm').submit();
    }
}
function updateCategory(){

    var submircheak = 0 ;
    var category_name = jQuery('#category_name').val();
    if (category_name == null || category_name==""){
        submircheak =1;
        setvalidation('category_name','F',"Please Enter Category title");
    }else{
         setvalidation('category_name','S','');
    }
   
    if(submircheak ==1){
       return false;
    }else{
        $('#updatecategory').submit();
    }
}



function createSubCategory(){

    var submircheak = 0 ;
    var category_id = jQuery('#category_id').val();
    if (category_id == null || category_id==""){
        submircheak =1;
        setvalidation('category_id','F',"Please Select  Category");
    }else{
         setvalidation('category_id','S','');
    }

    var sub_category_name = jQuery('#sub_category_name').val();
    if (sub_category_name == null || sub_category_name==""){
        submircheak =1;
        setvalidation('sub_category_name','F',"Please enter  Sub Category title");
    }else{
         setvalidation('sub_category_name','S','');
    }

    if(submircheak ==1){
       return false;
    }else{
        $('#subCategoryForm').submit();
    }
}
function updateSubCategory(){

    var submircheak = 0 ;
    var category_id = jQuery('#category_id').val();
    if (category_id == null || category_id==""){
        submircheak =1;
        setvalidation('category_id','F',"Please Select  Category");
    }else{
         setvalidation('category_id','S','');
    }

    var sub_category_name = jQuery('#sub_category_name').val();
    if (sub_category_name == null || sub_category_name==""){
        submircheak =1;
        setvalidation('sub_category_name','F',"Please enter  Sub Category title");
    }else{
         setvalidation('sub_category_name','S','');
    }

    if(submircheak ==1){
       return false;
    }else{
        $('#updateSubCategory').submit();
    }
}

function createSubSubCategory(){
    
    var submircheak = 0 ;
    var sub_cat_id = jQuery('#sub_cat_id').val();
    if (sub_cat_id == null || sub_cat_id==""){
        submircheak =1;
        setvalidation('sub_cat_id','F',"Please Select  Sub Category");
    }else{
         setvalidation('sub_cat_id','S','');
    }

    var sub_sub_category_name = jQuery('#sub_sub_category_name').val();
    if (sub_sub_category_name == null || sub_sub_category_name==""){
        submircheak =1;
        setvalidation('sub_sub_category_name','F',"Please enter  Sub Sub Category title");
    }else{
         setvalidation('sub_sub_category_name','S','');
    }

    if(submircheak ==1){
       return false;
    }else{
        $('#subSubCategoryForm').submit();
    }
}
function updateSubSubCategory(){
    
    var submircheak = 0 ;
    var sub_category_id = jQuery('#sub_category_id').val();
    if (sub_category_id == null || sub_category_id==""){
        submircheak =1;
        setvalidation('sub_category_id','F',"Please Select  Sub Category");
    }else{
         setvalidation('sub_category_id','S','');
    }

    var sub_sub_category_name = jQuery('#sub_sub_category_name').val();
    if (sub_sub_category_name == null || sub_sub_category_name==""){
        submircheak =1;
        setvalidation('sub_sub_category_name','F',"Please enter  Sub Sub Category title");
    }else{
         setvalidation('sub_sub_category_name','S','');
    }

    if(submircheak ==1){
       return false;
    }else{
        $('#updateSubSubCategory').submit();
    }
}





function createBrand(){

    var submircheak = 0 ;
    var brand_name = jQuery('#brand_name').val();
    if (brand_name == null || brand_name==""){
        submircheak =1;
        setvalidation('brand_name','F',"Please Enter title");
    }else{
         setvalidation('brand_name','S','');
    }
    if(submircheak ==1){
       return false;
    }else{
        $('#brandForm').submit();
    }
}



function loginFormValidation(){

    var submircheak = 0 ;

    var password = jQuery('#password').val();
    if (password == null || password==""){
        submircheak =1;
        setvalidation('password','F',"Please Enter Password");
    }else{
         setvalidation('password','S','');
    }

    var email_id = jQuery('#email_id').val();
    if (email_id == null || email_id==""){
        submircheak =1;
        setvalidation('email_id','F',"Please Enter email ID");
    }else{
         setvalidation('email_id','S','');
    }

    if(submircheak ==1){
       return false;
    }else{
        $('#checkLoginForm').submit();
    }
}
