<div class="merchr-hub-product-container">
	<div class="merchr-hub-product-container-inner">
		<div class="merchr-hub-product-control">
			<select id="merchr-category-filter">
				{$category_options}
			</select>
			<select id="merchr-collection-filter">
				{$collection_options}
			</select>
			<select id="merchr-industry-filter" style="display:none">
				{$industry_options}
			</select>
			<button id="merchr-filter-reset-btn" class="merchr-hub-btn">{$reset_button}</button>
			<input type="hidden" id="merchr-import-url" name="merchr_import_url" value="{$url}">
			{$nonce}
		</div>
		
		<div class="merchr-hub-product-container-grid">
			<div class="merchr-hub-product-section-title"{$store_products_display}><h2>{$store_title}</h2></div>
			<div class="merchr-hub-product-container-store">
				{$store_products}
			</div>
		</div>
	</div>
</div>
<div class="merchr-hub-import-products-btn-wrapper">
	<a class="merchr-hub-btn merchr-hub-import-products-btn">
		Import Your Products!
	</a>
	<div id="merchr-importer-error"></div>
</div>