<?php

namespace MerchrCust;

class HubConnect {
	
	private $store_public_api_key;
	private $user_id;
	private $user_key;

	public function __construct()
	{
		/*
		 * User hasn't been logged in yet. Use wp_head to call constructt
		 */
		add_action('wp_head', [ $this, 'construct' ]);
		add_action('wp_footer', [ $this, 'addJavaScriptToHead' ]);
	}
	
	public function construct()
	{
		$this->cacheUserData();
		$this->store_public_api_key = get_option('merchrcust_public_api_key', true);
	}
	
	public function addJavaScriptToHead()
	{
		?>
		<script type="text/javascript">
			window.merchrcust_ext_images_url = '<?php echo $this->extImagesUrl(); ?>';
		</script>
		<?php
	}
	
	private function cacheUserData()
	{
		$this->user_id = get_current_user_id();
		if (!$this->user_id){
			return;
		}
		$this->user_key = get_user_meta($this->user_id, 'merchrcust_key', true);
		if (!$this->user_key || empty($this->user_key)){
			$this->user_key = $this->generateHash();
			add_user_meta($this->user_id, 'merchrcust_key', $this->user_key);
		}
	}
	
	public function hubUrl($append = '')
	{
		return MERCHR_HUB_API_URL . $append;
	}

	public function extUrl($middle)
	{
		$root_ext_url = $this->hubUrl($middle);
		$query = [
			'external_user_id'		=> $this->user_id,
			'external_user_key'		=> $this->user_key,
			'store_public_api_key'	=> $this->store_public_api_key,
		];
		return $root_ext_url . '?' . http_build_query($query);
		
	}
	
	public function extImagesUrl()
	{
		if (!$this->user_id){
			return '';
		}
		return $this->extUrl('/#/ext/images');
	}

	private function generateHash()
	{
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$length = rand(31,63);
		$input_length = strlen($chars);
		$random_string = '';
		for($i = 0; $i < $length; $i++) {
			$random_string .= $chars[mt_rand(0, $input_length - 1)];
		}
		return $random_string;
	}
	
}