<div class="merchr-select-your-store-wrapper">
	<div class="merchr-select-your-store-inner">
		<h3>{$title}</h3>
		<p>{$message}</p>
		
		<form id="merchr-hub-select-store-form" class="merchr-hub-form" action="{$action}" data-handle="page" data-target="#merchr-hub-product-live-content" data-clear="#merchr-hub-live-content" data-notice-element=".merchr-hub-select-store-form-notice-wrapper" autocomplete="off">
					
					<select class="merchr-required" name="store" title="{$store_field_title}">
						{$options}
					</select>
					
					<input type="hidden" name="the_stores" value='{$the_stores}'>
					{$nonce}
					<div>
						<input class="merchr-hub-submit" type="submit" title="{$submit_title}" value="{$submit_value}" />
					</div>
					<div class="merchr-hub-select-store-form-notice-wrapper"></div>
		</form>
	</div>
</div>