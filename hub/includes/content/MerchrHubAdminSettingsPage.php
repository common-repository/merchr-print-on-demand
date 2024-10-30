<?php
/**
 * Merchr Hub Settings Page.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes/content
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes\content;

use MerchrHub\includes\MerchrHubContent;
use MerchrHub\includes\data\MerchrHubAdminFormsData;
use MerchrHub\includes\helpers\MerchrHubHelpersTemplates;
use MerchrHub\includes\traits\MerchrHubDatabaseInteraction;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubAdminSettingsPage extends MerchrHubContent
{
	use MerchrHubDatabaseInteraction;
	
	/**
	 * Set up class.
	 */
	public function __construct() 
	{
		parent::__construct();
		
		// Setup db
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->getMerchrTables();
	}
	
	/**
	 * Return Settings Form.
	 */
	public function returnSettingsForm()
	{
		$forms = new MerchrHubAdminFormsData();
		return MerchrHubHelpersTemplates::parseStringReplacements(
			MerchrHubHelpersTemplates::fetchTemplateContents('settings_form.tpl', $this->templates_path), 
			$forms->getSettingsPageFormsData($this->options)
		);
	}
}
