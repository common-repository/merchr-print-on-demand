<div class="merchr-hub-settings-forms">
	<div class="merchr-hub-form-wrapper">
		<div class="merchr-hub-form-inner">
			<form id="merchr-hub-settings-form" class="merchr-hub-form" action="{$settings_action}"data-handle="form" data-target="" data-clear="" data-notice-element=".merchr-hub-settings-form-notice-wrapper" autocomplete="off">
				<div class="merchr-hub-form-message">
					<h2>{$settings_header}</h2>
				</div>
				
				<label for="prices_include_tax">{$tax_data_label}</label>
				<select id="prices_include_tax" name="prices_include_tax" class="merchr-tooltip" title="{$tax_data_title}">
					{$tax_data_options}
				</select>
				
				<label for="description">{$description_label}</label>
				<select id="description" name="description" class="merchr-tooltip" title="{$description_title}">
					{$description_options}
				</select>
				
				<label for="remove_data">{$remove_data_label}</label>
				<select id="remove_data" name="remove_data" class="merchr-tooltip" title="{$remove_data_title}">
					{$remove_data_options}
				</select>
				
				<hr>
				
				<label for="api_key">{$api_key_label}</label>
				<input id="api_key" type="text" name="api_key" placeholder="{$api_key_field_placeholder}" class="merchr-tooltip" title="{$api_key_field_title}" value="" />
				<br>
				
				{$settings_nonce}
				<input class="merchr-hub-submit" type="submit" title="{$settings_submit_title}" value="{$settings_submit_value}" />
				<div class="merchr-hub-settings-form-notice-wrapper"></div>
			</form>
		</div>
	</div>
</div>