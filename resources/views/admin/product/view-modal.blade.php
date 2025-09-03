<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name:</dt>
                    <dd class="col-sm-8" id="view_product_name"></dd>
                    <dt class="col-sm-4">Category:</dt>
                    <dd class="col-sm-8" id="view_product_category"></dd>
                    <dt class="col-sm-4">Brand:</dt>
                    <dd class="col-sm-8" id="view_product_brand"></dd>
                    <dt class="col-sm-4">SKU:</dt>
                    <dd class="col-sm-8" id="view_product_sku"></dd>
                    <dt class="col-sm-4">Barcode:</dt>
                    <dd class="col-sm-8" id="view_product_barcode"></dd>
                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8" id="view_product_status"></dd>
                    <dt class="col-sm-4">Description:</dt>
                    <dd class="col-sm-8" id="view_product_description"></dd>
                    <dt class="col-sm-4">Images:</dt>
                    <dd class="col-sm-8" id="view_product_images">
                        <!-- Images will be loaded here -->
                    </dd>
                    <dt class="col-sm-4">Store:</dt>
                    <dd class="col-sm-8" id="view_product_store"></dd>
                    <dt class="col-sm-4">Base Unit:</dt>
                    <dd class="col-sm-8" id="view_product_base_unit"></dd>
                    <dt class="col-sm-4">Purchase Cost:</dt>
                    <dd class="col-sm-8" id="view_product_purchase_cost"></dd>
                    <dt class="col-sm-4">Sales Price:</dt>
                    <dd class="col-sm-8" id="view_product_sales_price"></dd>
                </dl>
                <hr>
                <h6>Additional Fields</h6>
                <dl class="row mb-0" id="view_product_additional_fields">
                    <!-- Additional fields will be loaded here -->
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /View Product Modal -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    var viewModal = document.getElementById('viewProductModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            document.getElementById('view_product_name').textContent = button.getAttribute('data-name');
            document.getElementById('view_product_category').textContent = button.getAttribute('data-category');
            document.getElementById('view_product_brand').textContent = button.getAttribute('data-brand');
            document.getElementById('view_product_sku').textContent = button.getAttribute('data-sku');
            document.getElementById('view_product_barcode').textContent = button.getAttribute('data-barcode');
            document.getElementById('view_product_status').textContent = button.getAttribute('data-status') == 1 ? 'Active' : 'Inactive';
            document.getElementById('view_product_description').textContent = button.getAttribute('data-description');
            document.getElementById('view_product_store').textContent = button.getAttribute('data-store') || '-';
            document.getElementById('view_product_base_unit').textContent = button.getAttribute('data-base_unit') || '-';
            document.getElementById('view_product_purchase_cost').textContent = button.getAttribute('data-purchase_cost') || '-';
            document.getElementById('view_product_sales_price').textContent = button.getAttribute('data-sales_price') || '-';

            // Images (expects a JSON array of URLs in data-images)
            var imagesData = button.getAttribute('data-images');
            var imagesContainer = document.getElementById('view_product_images');
            imagesContainer.innerHTML = '';
            if (imagesData) {
                try {
                    var images = JSON.parse(imagesData);
                    if (Array.isArray(images) && images.length > 0) {
                        images.forEach(function(url) {
                            var img = document.createElement('img');
                            img.src = url;
                            img.alt = 'Product Image';
                            img.style.maxWidth = '60px';
                            img.style.maxHeight = '60px';
                            img.className = 'me-1 mb-1 border rounded';
                            imagesContainer.appendChild(img);
                        });
                    } else {
                        imagesContainer.textContent = 'No images';
                    }
                } catch (e) {
                    imagesContainer.textContent = 'No images';
                }
            } else {
                imagesContainer.textContent = 'No images';
            }

            // Additional Fields (expects a JSON object in data-additional_fields)
            var additionalFieldsData = button.getAttribute('data-additional_fields');
            var additionalFieldsContainer = document.getElementById('view_product_additional_fields');
            additionalFieldsContainer.innerHTML = '';
            if (additionalFieldsData) {
                try {
                    var fields = JSON.parse(additionalFieldsData);
                    if (fields && typeof fields === 'object') {
                        Object.keys(fields).forEach(function(label) {
                            var dt = document.createElement('dt');
                            dt.className = 'col-sm-4';
                            dt.textContent = label + ':';
                            var dd = document.createElement('dd');
                            dd.className = 'col-sm-8';
                            dd.textContent = fields[label];
                            additionalFieldsContainer.appendChild(dt);
                            additionalFieldsContainer.appendChild(dd);
                        });
                    }
                } catch (e) {
                    // Ignore if invalid
                }
            }
        });
    }
});
</script>