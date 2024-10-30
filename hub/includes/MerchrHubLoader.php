<?php
/**
 * Merchr Hub Loader Class.
 *
 * @since      1.0.0
 * @package    Merchr
 * @subpackage Merchr/includes
 * @author     Merchr Limited <admin@merchr.co.uk>
*/
namespace MerchrHub\includes;

// If this file is called directly, abort.
if(!defined( 'WPINC')) { die; }

class MerchrHubLoader
{
	protected $actions;
	protected $filters;
	
	/*
     * Set protected vars as arrays
    */
	public function __construct() 
	{
		$this->actions = [];
		$this->filters = [];
	}
	
	/*
     * Add WordPress action to array
	 *
	 * @param string
	 * @param object
	 * @param string
	 * @param int optional
	 * @param int optional
    */
	public function addAction(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1) 
	{
		$this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
	}
	
	/*
     * Add WordPress filter to array
	 *
	 * @param string
	 * @param object
	 * @param string
	 * @param int optional
	 * @param int optional
    */
	public function addFilter(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1) 
	{
		$this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
	}
	
	/*
     * A utility function that is used to register the actions and hooks into a single collection.
	 * 
	 * @param array
	 * @param string
	 * @param object
	 * @param string
	 * @param int optional
	 * @param int optional
	 *
	 * @return array   
    */
	private function add(array $hooks, string $hook, $component, string $callback, int $priority, int $accepted_args) 
	{
		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		];
		return $hooks;
	}
	
	/**
	 * Register the filters and actions with WordPress.
	 */
	public function run() 
	{
		foreach($this->filters as $hook) {
			add_filter($hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args']);
		}
		foreach($this->actions as $hook) {
			add_action($hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args']);
		}
	}
}
