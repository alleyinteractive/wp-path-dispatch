<?php
/**
 * This class is a custom dispatch system.
 *
 * @package WP_Path_Dispatch
 * @version 1.0.0
 */

namespace WP_Path_Dispatch;

/**
 * Path Dispatch
 */
class Path_Dispatch {

	/**
	 * Query vars that should be allowed.
	 *
	 * @var array
	 */
	public $qv = [ 'dispatch' ];

	/**
	 * Array of basic paths.
	 *
	 * @var array
	 */
	public $basic_paths = [];

	/**
	 * Array of rewrite paths.
	 *
	 * @var array
	 */
	public $rewrite_paths = [];

	/**
	 * Instance of this class.
	 *
	 * @var Path_Dispatch
	 */
	private static $instance;

	/**
	 * Don't allow __clone.
	 */
	public function __clone() {
		wp_die( "Please don't __clone Path_Dispatch" );
	}

	/**
	 * Don't allow __wakeup.
	 */
	public function __wakeup() {
		wp_die( "Please don't __wakeup Path_Dispatch" );
	}

	/**
	 * Return the only instance of this class.
	 *
	 * @return Path_Dispatch
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Path_Dispatch();
		}
		return self::$instance;
	}

	/**
	 * Clear the instance of this class.
	 */
	public static function clear_instance() {
		self::$instance = null;
	}

	/**
	 * Setup class for the first time.
	 */
	protected function __construct() {
		// Add our query_var, 'dispatch'.
		add_filter( 'query_vars', [ $this, 'add_query_var' ] );

		// Setup rewrite rules for our paths.
		add_action( 'init', [ $this, 'add_rewrite_rules' ], 5 );

		// We're doing this on parse_query to ensure that query vars are set.
		add_action( 'parse_query', [ $this, 'dispatch_path' ] );
	}

	/**
	 * Add a path. This method is the money maker; pass it an array with at least the 'path' key set
	 * (or a string, which will become an array).
	 *
	 * @param string|array $args {
	 *      If string, becomes [ 'path' => $args ]. Otherwise, 'path' must be set. In addition to
	 *      the keys mentioned below, you can pass any other key => value pairs. This whole array will be
	 *      passed when the action fires, so you'll be able to access your data at that time.
	 *
	 *      @type string   $path     The dispatch path. This will be added as a rewrite rule, "($path)/?$".
	 *      @type callback $callback Optional. A valid callback function.
	 *      @type string   $action   Optional. The action to fire instead of "dispatch_path_{$path}". This action
	 *                               will still be passed this array of $args.
	 *      @type array    $rewrite {
	 *          Optional. Add a custom rewrite rule and optionally register query vars.
	 *          @see http://codex.wordpress.org/Rewrite_API/add_rewrite_rule
	 *
	 *          @type string       $rule       The rewrite rule.
	 *          @type string       $redirect   Optional. The URL you would like to fetch. Default is 'index.php?dispatch=$matches[1]'
	 *          @type string       $position   Optional. The rewrite rule position. Default is 'top'.
	 *          @type string|array $query_vars Optional. Query var(s) to register.
	 *                                         @see http://codex.wordpress.org/Plugin_API/Filter_Reference/query_vars
	 *      }
	 * }
	 */
	public function add_path( $args = [] ) {
		if ( is_string( $args ) && ! empty( $args ) ) {
			$args = [
				'path' => $args,
			];
		}

		if ( ! empty( $args['path'] ) ) {
			$path = $args['path'];

			if ( ! empty( $args['rewrite'] ) ) {
				$this->rewrite_paths[ $path ] = $args;
				if ( ! empty( $args['rewrite']['query_vars'] ) ) {
					$this->qv = array_merge( $this->qv, (array) $args['rewrite']['query_vars'] );
				}
			} else {
				$this->basic_paths[ $path ] = $args;
			}

			if ( ! empty( $args['callback'] ) ) {
				add_action( 'dispatch_path_' . $path, $args['callback'] );
			}
		}
	}

	/**
	 * Add multiple paths in one call.
	 *
	 * @see Path_Dispatch::add_path
	 *
	 * @param array $paths An array of arrays that would be passed to add_path.
	 */
	public function add_paths( $paths ) {
		foreach ( $paths as $path ) {
			$this->add_path( $path );
		}
	}

	/**
	 * Add the class query var "dispatch" as well as any others added through add_path.
	 *
	 * @param array $qv The current query vars.
	 * @return array The modified query vars.
	 */
	public function add_query_var( $qv ) {
		return array_merge( $qv, $this->qv );
	}

	/**
	 * Add rewrite rules for our dispatched paths.
	 */
	public function add_rewrite_rules() {
		if ( ! empty( $this->basic_paths ) ) {
			$slugs = array_map( 'preg_quote', array_keys( $this->basic_paths ) );
			$slugs = implode( '|', $slugs );
			add_rewrite_rule( "($slugs)/?$", 'index.php?dispatch=$matches[1]', 'top' );
		}

		if ( ! empty( $this->rewrite_paths ) ) {
			foreach ( $this->rewrite_paths as $args ) {
				if ( ! empty( $args['rewrite']['rule'] ) ) {
					if ( empty( $args['rewrite']['redirect'] ) ) {
						$args['rewrite']['redirect'] = 'index.php?dispatch=$matches[1]';
					}
					if ( empty( $args['rewrite']['position'] ) ) {
						$args['rewrite']['position'] = 'top';
					}
					add_rewrite_rule( $args['rewrite']['rule'], $args['rewrite']['redirect'], $args['rewrite']['position'] );
				}
			}
		}
	}

	/**
	 * Trigger an action when a dispatched path is requested. Also, potentially load
	 * a template if that was set.
	 *
	 * @param  array $query Dispatch query.
	 */
	public function dispatch_path( &$query ) {
		$path = get_query_var( 'dispatch' );
		if ( $query->is_main_query() && $path ) {
			if ( ! empty( $this->basic_paths[ $path ] ) ) {
				$args = $this->basic_paths[ $path ];
			} elseif ( ! empty( $this->rewrite_paths[ $path ] ) ) {
				$args = $this->rewrite_paths[ $path ];
			}
			if ( empty( $args['action'] ) ) {
				do_action( 'dispatch_path_' . $path, $args ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			} else {
				do_action( $args['action'], $args ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			}

			if ( ! empty( $args['template'] ) ) {
				get_template_part( 'dispatch', $args['template'] );
				exit;
			}
		}
	}
}
