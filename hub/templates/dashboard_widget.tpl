<img class="merchr-dashboard-widget-logo" src="{$logo_src}" alt="{$logo_alt}" title="{$logo_title}">
<h2 class="merchr-dashboard-widget-welcome">{$welcome_message}</h2>
<div>
	<div class="merchr-hub-dashboard-stats">
		<h3><span>{$currency_symbol}</span>{$orders_value}</h3>
		<span>{$imported_products_title}:</span> {$imported_products}<br>
		<span>{$orders_total_title}:</span> {$orders_total}
		<div{$failed_class}>
			<br>
			<span>{$orders_failed_title}:</span> {$orders_failed}<br>
			{$failed_link}
		</div>
	</div>
</div>
<a class="merchr-hub-btn merchr-dashboard-widget-link-button merchr-dashboard-widget-link-button-sml" href="{$manage_link}" title="{$manage_link_text}">{$manage_link_text}</a>