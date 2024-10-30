<div class="merchr-hub-product-card{$merchr_product_imported_class}{$merchr_product_importing_class}" id="merchr-hub-product-card-{$id}" data-id="{$id}" data-type="{$type}" data-categories='{$categories}' data-collections='{$collections}' data-industries='{$industries}'>
	<div class="merchr-hub-product-card-inner" id="merchr-hub-product-id-{$id}" data-id="{$id}">
		<div class="merchr-hub-product-imported-tag">{$imported}</div>
		<div class="merchr-hub-product-importing-tag">{$importing}</div>
		<div class="merchr-hub-product-card-title"><h3>{$title}</h3></div>
		<div class="merchr-hub-product-card-img"><img src="{$img}" alt="{$title}" title="{$title}"></div>
		<div class="merchr-hub-product-card-price">{$price}</div>
		<div class="merchr-hub-product-card-description">{$description}</div>
		<div class="merchr-hub-product-card-buttons">
			<a class="merchr-hub-btn merchr-hub-btn-product-select" data-id="{$id}" data-alt-text="{$select_alt_text}">{$select_button_text}</a>
		</div>
		<div class="merchr-hub-product-card-hidden-data">
			<div class="merchr-hub-form" id="merchr-hub-product-edit-{$id}">
				<div class="merchr-edit-title">{$product_name_title}</div>
				<input class="product-title-field" type="text" name="title" value="{$title}">
				<div class="merchr-edit-title">{$product_description_title}</div>
				<textarea class="product-description-field"type="text" name="description" rows="4">{$description_field}</textarea>
				<div class="merchr-edit-title">{$product_selling_price_title}</div>
				<input class="product-price-field" type="text" name="price" value="{$price}">
				
				<div class="merchr-two-halfs">
					<div>
						<div class="merchr-edit-title">{$product_rrp_title}</div>
						<input class="product-rrp-field" type="text" name="rrp" value="{$price}" disabled>
					</div>
					<div>
						<div class="merchr-edit-title">{$product_cost_title}</div>
						<input class="product-cost-field" type="text" name="cost" value="{$cost}" disabled>
					</div>
				</div>
				<div class="merchr-two-halfs">
					<div>
						<div class="merchr-edit-title">{$product_markup_title}</div>
						<input class="product-markup-field" type="text" name="markup" value="{$markup}" disabled>
					</div>
					<div>
						<div class="merchr-edit-title">{$product_markup_percentage_title}</div>
						<input class="product-markup-percentage-field" type="text" name="markup_percentage" value="{$markup_percentage}" disabled>
					</div>
				</div>
				
				<div class="merchr-negative-profit-wrapper">
					<input type="checkbox" class="accept_negative_profit" name="accept_negative_profit" value="Yes"> {$accept_negative_profit}
				</div>
				
				<button class="merchr-hub-btn" data-id="{$id}">{$save}</button>
				
				<div class="merchr-edit-product-error"></div>
			</div>
		</div>
	</div>
</div>