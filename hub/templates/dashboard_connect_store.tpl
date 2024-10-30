<div class="merchr-hub-store-connect-forms">
	<div class="merchr-hub-form-wrapper">
		<div class="merchr-hub-form-inner">
			<form id="merchr-hub-api-form" class="merchr-hub-form" action="{$api_action}" data-handle="page" data-target="#merchr-hub-product-live-content" data-clear="#merchr-hub-live-content" data-notice-element=".merchr-hub-api-form-notice-wrapper" autocomplete="off">
				<div class="merchr-hub-form-message">
					<h2>{$api_header}</h2>
					<p>{$api_message}</p>
				</div>
				
				<input class="merchr-required" type="text" name="api" placeholder="{$api_field_placeholder}" title="{$api_field_title}" value="" />
				
				{$api_nonce}
				<input class="merchr-hub-submit" type="submit" title="{$api_submit_title}" value="{$api_submit_value}" />
				<div class="merchr-hub-api-form-notice-wrapper"></div>
				
				<p id="merchr-no-api-key">{$api_no_api_key}</p>
			</form>
			
			<form id="merchr-hub-account-form" class="merchr-hub-form" action="{$account_action}" data-handle="page" data-target="#merchr-hub-product-live-content" data-clear="#merchr-hub-live-content" data-notice-element=".merchr-hub-account-form-notice-wrapper" autocomplete="off">
				<div class="merchr-hub-form-message">
					<h2>{$account_header}</h2>
					<p>{$account_message}</p>
				</div>
				
				<input class="merchr-required" type="text" name="full_name" placeholder="{$account_name_placeholder}" title="{$account_name_title}" value="" />
				<input class="merchr-required" type="email" name="email" placeholder="{$account_email_placeholder}" title="{$account_email_title}" value="" autoComplete='off' />
				<input class="merchr-required" type="password" name="password" placeholder="{$account_password_placeholder}" title="{$account_password_title}" value="" autocomplete="new-password" />
				<input type="hidden" name="store_name" placeholder="{$account_store_name_placeholder}" title="{$account_store_name_title}" value="{$account_store_name_value}" />
				<input type="hidden" name="store_url" placeholder="{$account_store_url_placeholder}" title="{$account_store_url_title}" value="{$account_store_url_value}" />
				<input type="hidden" name="store_email" placeholder="{$account_store_email_placeholder}" title="{$account_store_email_title}" value="{$account_store_email_value}" />
				<input type="hidden" name="store_logo" placeholder="{$account_logo_placeholder}" title="{$account_logo_title}" value="{$account_logo_value}" />
				
				{$account_nonce}
				<input class="merchr-hub-submit" type="submit" title="{$account_submit_title}" value="{$account_submit_value}" />
				<div class="merchr-hub-account-form-notice-wrapper"></div>
				
				<p id="merchr-has-api-key">{$account_has_api_key}</p>
			</form>
		</div>
	</div>
</div>