<?php
/**
 * Merchr DB Upgrade 1.0.1
*/
namespace MerchrHub\includes\updates;

class MerchrUpgradeDatabase
{
    protected $wpdb; // @var WPDB
    protected $prefix;  // @var string
    protected $charset_collate; // @var string
    
    /**
	 * 
	 */
	public function __construct(
        $wpdb, 
        string $prefix, 
        string $charset_collate
    ) 
	{
		$this->wpdb = $wpdb;
        $this->prefix = $prefix;
        $this->charset_collate = $charset_collate;
	}
    
    /**
	 * Run the upgrade.
	 */
	public function run()
	{
        $sql = "ALTER TABLE `{$this->prefix}merchr_hub_import_products` 
                MODIFY COLUMN `product_categories` longtext {$this->charset_collate} NULL AFTER `product_customisable`,
                MODIFY COLUMN `product_collections` longtext {$this->charset_collate} NULL AFTER `product_categories`,
                MODIFY COLUMN `product_industries` longtext {$this->charset_collate} NULL AFTER `product_collections`;";

        $this->wpdb->query($sql);
    }
}