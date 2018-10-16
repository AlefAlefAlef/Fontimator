<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	protected $dependencies;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();
		$this->shortcodes = array();
		$this->dependencies = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    2.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    2.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new dependency to the collection to be checked upon loading.
	 *
	 * @since     1.0.0
	 * @param     string        $type           Either class, function, constant or plugin.
	 * @param     string        $value          The name of the component which is required.
	 */
	public function add_dependency( $type, $value ) {
		$this->dependencies[] = array( $type, $value );
	}

	/**
	 * Add a new shortcode to the collection to be registered with WordPress
	 *
	 * @since     1.0.0
	 * @param     string        $tag           The name of the new shortcode.
	 * @param     object        $component      A reference to the instance of the object on which the shortcode is defined.
	 * @param     string        $callback       The name of the function that defines the shortcode.
	 */
	public function add_shortcode( $tag, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->shortcodes = $this->add( $this->shortcodes, $tag, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->shortcodes as $hook ) {
			add_shortcode( $hook['hook'], array( $hook['component'], $hook['callback'] ) );
		}

	}

	public function check_dependencies() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
			foreach ( $this->dependencies as $dependency ) {
				if ( 'plugin' === $dependency[0] && is_plugin_active( $dependency[1] ) ) {
					continue;
				}
				if ( 'class' === $dependency[0] && class_exists( $dependency[1] ) ) {
					continue;
				}
				if ( 'function' === $dependency[0] && function_exists( $dependency[1] ) ) {
					continue;
				}
				if ( 'constant' === $dependency[0] && defined( $dependency[1] ) ) {
					continue;
				}

				// (Else)

				add_action(
					'admin_notices', function() use ( $dependency ) {
						$this->missing_dependency_notice( $dependency );
					}
				);
			}

			// deactivate_plugins( plugin_basename( __FILE__ ) );

			// if ( isset( $_GET['activate'] ) ) {
			// 	unset( $_GET['activate'] );
			// }
		}

	}

	protected function missing_dependency_notice( $dependency ) {
		?>
		<div class="error">
			<p>Sorry, but The Fontimator requires the <code><?php echo esc_html( $dependency[1] ); ?></code> <?php echo esc_html( $dependency[0] ); ?> to be available.</p>
		</div>
		<?php
	}

}
