<?php
/**
 * Plugin Name: YT Custom Post Status Manager
 * Plugin URI: https://github.com/krasenslavov/yt-custom-post-status-manager
 * Description: Add and manage custom post statuses with color-coded admin interface.
 * Version: 1.0.1
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Krasen Slavov
 * Author URI: https://krasenslavov.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yt-custom-post-status-manager
 * Domain Path: /languages
 *
 * @package YT_Custom_Post_Status_Manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version.
 */
define( 'YT_CPSM_VERSION', '1.0.0' );

/**
 * Plugin base name.
 */
define( 'YT_CPSM_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin directory path.
 */
define( 'YT_CPSM_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'YT_CPSM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class YT_Custom_Post_Status_Manager {

	/**
	 * Single instance.
	 *
	 * @var YT_Custom_Post_Status_Manager|null
	 */
	private static $instance = null;

	/**
	 * Custom statuses.
	 *
	 * @var array
	 */
	private $statuses = array();

	/**
	 * Get instance.
	 *
	 * @return YT_Custom_Post_Status_Manager
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->statuses = get_option( 'yt_cpsm_statuses', array() );
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_custom_statuses' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'add_quick_edit_script' ) );
		add_action( 'admin_footer-post.php', array( $this, 'add_post_status_script' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'add_post_status_script' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'handle_status_actions' ) );
			add_filter( 'display_post_states', array( $this, 'display_custom_state' ), 10, 2 );
			add_action( 'restrict_manage_posts', array( $this, 'add_status_filter' ) );
			add_filter( 'parse_query', array( $this, 'filter_posts_by_status' ) );
			add_filter( 'plugin_action_links_' . YT_CPSM_BASENAME, array( $this, 'add_action_links' ) );
		}
	}

	/**
	 * Load text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'yt-custom-post-status-manager', false, dirname( YT_CPSM_BASENAME ) . '/languages' );
	}

	/**
	 * Register custom post statuses.
	 */
	public function register_custom_statuses() {
		foreach ( $this->statuses as $status ) {
			register_post_status(
				$status['slug'],
				array(
					'label'                     => $status['label'],
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( $status['label'] . ' <span class="count">(%s)</span>', $status['label'] . ' <span class="count">(%s)</span>', 'yt-custom-post-status-manager' ), // phpcs:ignore
				)
			);
		}
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'edit.php' === $hook || 'post.php' === $hook || 'post-new.php' === $hook ) {
			wp_enqueue_style( 'yt-cpsm-admin', YT_CPSM_URL . 'assets/css/yt-custom-post-status-manager.css', array(), YT_CPSM_VERSION );
			wp_enqueue_script( 'yt-cpsm-admin', YT_CPSM_URL . 'assets/js/yt-custom-post-status-manager.js', array( 'jquery' ), YT_CPSM_VERSION, true );
			wp_localize_script( 'yt-cpsm-admin', 'ytCpsmData', array( 'statuses' => $this->get_statuses_for_js() ) );
		}
	}

	/**
	 * Get statuses formatted for JS.
	 *
	 * @return array
	 */
	private function get_statuses_for_js() {
		$formatted = array();
		foreach ( $this->statuses as $status ) {
			$formatted[ $status['slug'] ] = array(
				'label' => $status['label'],
				'color' => $status['color'],
			);
		}
		return $formatted;
	}

	/**
	 * Add quick edit script.
	 */
	public function add_quick_edit_script() {
		global $post_type;
		if ( 'post' !== $post_type && 'page' !== $post_type ) {
			return;
		}
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#the-list').on('click', '.editinline', function() {
				const postId = $(this).closest('tr').attr('id').replace('post-', '');
				const status = $('#inline_' + postId + ' ._status').text();
				$('.inline-edit-status select[name="_status"]').val(status);
			});
		});
		</script>
		<?php
	}

	/**
	 * Add post status script.
	 */
	public function add_post_status_script() {
		global $post;
		if ( ! $post ) {
			return;
		}
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			<?php foreach ( $this->statuses as $status ) : ?>
			$('#post-status-select').find('select').append(
				$('<option></option>').val('<?php echo esc_js( $status['slug'] ); ?>').text('<?php echo esc_js( $status['label'] ); ?>')
			);
			<?php endforeach; ?>
			<?php if ( in_array( $post->post_status, array_column( $this->statuses, 'slug' ), true ) ) : ?>
			$('#post-status-select').find('select').val('<?php echo esc_js( $post->post_status ); ?>');
			$('#post-status-display').text('<?php echo esc_js( $this->get_status_label( $post->post_status ) ); ?>');
			<?php endif; ?>
		});
		</script>
		<?php
	}

	/**
	 * Get status label by slug.
	 *
	 * @param string $slug Status slug.
	 * @return string
	 */
	private function get_status_label( $slug ) {
		foreach ( $this->statuses as $status ) {
			if ( $status['slug'] === $slug ) {
				return $status['label'];
			}
		}
		return '';
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'Post Status Manager', 'yt-custom-post-status-manager' ),
			__( 'Post Status', 'yt-custom-post-status-manager' ),
			'manage_options',
			'yt-post-status-manager',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Handle status actions.
	 */
	public function handle_status_actions() {
		if ( ! isset( $_POST['yt_cpsm_action'], $_POST['yt_cpsm_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['yt_cpsm_nonce'], 'yt_cpsm_action' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'yt-custom-post-status-manager' ) );
		}

		$action = sanitize_text_field( $_POST['yt_cpsm_action'] );

		if ( 'add' === $action ) {
			$this->add_status();
		} elseif ( 'edit' === $action ) {
			$this->edit_status();
		} elseif ( 'delete' === $action ) {
			$this->delete_status();
		}

		wp_safe_redirect( admin_url( 'options-general.php?page=yt-post-status-manager&updated=1' ) );
		exit;
	}

	/**
	 * Add new status.
	 */
	private function add_status() {
		$slug  = sanitize_title( $_POST['status_slug'] ?? '' );
		$label = sanitize_text_field( $_POST['status_label'] ?? '' );
		$color = sanitize_hex_color( $_POST['status_color'] ?? '#cccccc' );

		if ( empty( $slug ) || empty( $label ) ) {
			return;
		}

		$this->statuses[] = array(
			'slug'  => $slug,
			'label' => $label,
			'color' => $color,
		);

		update_option( 'yt_cpsm_statuses', $this->statuses );
	}

	/**
	 * Edit existing status.
	 */
	private function edit_status() {
		$index = isset( $_POST['status_index'] ) ? absint( $_POST['status_index'] ) : -1;
		$label = sanitize_text_field( $_POST['status_label'] ?? '' );
		$color = sanitize_hex_color( $_POST['status_color'] ?? '#cccccc' );

		if ( $index >= 0 && isset( $this->statuses[ $index ] ) && ! empty( $label ) ) {
			$this->statuses[ $index ]['label'] = $label;
			$this->statuses[ $index ]['color'] = $color;
			update_option( 'yt_cpsm_statuses', $this->statuses );
		}
	}

	/**
	 * Delete status.
	 */
	private function delete_status() {
		$index = isset( $_POST['status_index'] ) ? absint( $_POST['status_index'] ) : -1;

		if ( $index >= 0 && isset( $this->statuses[ $index ] ) ) {
			array_splice( $this->statuses, $index, 1 );
			update_option( 'yt_cpsm_statuses', $this->statuses );
		}
	}

	/**
	 * Add action links.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=yt-post-status-manager' ) ) . '">' . esc_html__( 'Settings', 'yt-custom-post-status-manager' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Render admin page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions.', 'yt-custom-post-status-manager' ) );
		}
		$edit_index  = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : -1;
		$edit_status = $edit_index >= 0 && isset( $this->statuses[ $edit_index ] ) ? $this->statuses[ $edit_index ] : null;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Custom Post Status Manager', 'yt-custom-post-status-manager' ); ?></h1>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Status updated successfully.', 'yt-custom-post-status-manager' ); ?></p></div>
			<?php endif; ?>

			<div class="yt-cpsm-container">
				<div class="yt-cpsm-form">
					<h2><?php echo $edit_status ? esc_html__( 'Edit Status', 'yt-custom-post-status-manager' ) : esc_html__( 'Add New Status', 'yt-custom-post-status-manager' ); ?></h2>
					<form method="post">
						<?php wp_nonce_field( 'yt_cpsm_action', 'yt_cpsm_nonce' ); ?>
						<input type="hidden" name="yt_cpsm_action" value="<?php echo $edit_status ? 'edit' : 'add'; ?>">
						<?php if ( $edit_status ) : ?>
							<input type="hidden" name="status_index" value="<?php echo esc_attr( $edit_index ); ?>">
						<?php endif; ?>

						<table class="form-table">
							<?php if ( ! $edit_status ) : ?>
							<tr>
								<th><label for="status_slug"><?php esc_html_e( 'Status Slug', 'yt-custom-post-status-manager' ); ?></label></th>
								<td><input type="text" id="status_slug" name="status_slug" class="regular-text" required></td>
							</tr>
							<?php endif; ?>
							<tr>
								<th><label for="status_label"><?php esc_html_e( 'Status Label', 'yt-custom-post-status-manager' ); ?></label></th>
								<td><input type="text" id="status_label" name="status_label" value="<?php echo esc_attr( $edit_status['label'] ?? '' ); ?>" class="regular-text" required></td>
							</tr>
							<tr>
								<th><label for="status_color"><?php esc_html_e( 'Status Color', 'yt-custom-post-status-manager' ); ?></label></th>
								<td><input type="color" id="status_color" name="status_color" value="<?php echo esc_attr( $edit_status['color'] ?? '#cccccc' ); ?>"></td>
							</tr>
						</table>
						<?php submit_button( $edit_status ? __( 'Update Status', 'yt-custom-post-status-manager' ) : __( 'Add Status', 'yt-custom-post-status-manager' ) ); ?>
					</form>
				</div>

				<div class="yt-cpsm-list">
					<h2><?php esc_html_e( 'Existing Statuses', 'yt-custom-post-status-manager' ); ?></h2>
					<?php if ( empty( $this->statuses ) ) : ?>
						<p><?php esc_html_e( 'No custom statuses yet.', 'yt-custom-post-status-manager' ); ?></p>
					<?php else : ?>
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Slug', 'yt-custom-post-status-manager' ); ?></th>
									<th><?php esc_html_e( 'Label', 'yt-custom-post-status-manager' ); ?></th>
									<th><?php esc_html_e( 'Color', 'yt-custom-post-status-manager' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'yt-custom-post-status-manager' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $this->statuses as $index => $status ) : ?>
									<tr>
										<td><code><?php echo esc_html( $status['slug'] ); ?></code></td>
										<td><?php echo esc_html( $status['label'] ); ?></td>
										<td><span class="yt-cpsm-color-box" style="background-color: <?php echo esc_attr( $status['color'] ); ?>"></span> <?php echo esc_html( $status['color'] ); ?></td>
										<td>
											<a href="<?php echo esc_url( admin_url( 'options-general.php?page=yt-post-status-manager&edit=' . $index ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'yt-custom-post-status-manager' ); ?></a>
											<form method="post" style="display:inline;">
												<?php wp_nonce_field( 'yt_cpsm_action', 'yt_cpsm_nonce' ); ?>
												<input type="hidden" name="yt_cpsm_action" value="delete">
												<input type="hidden" name="status_index" value="<?php echo esc_attr( $index ); ?>">
												<button type="submit" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'yt-custom-post-status-manager' ); ?>')"><?php esc_html_e( 'Delete', 'yt-custom-post-status-manager' ); ?></button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display custom state.
	 *
	 * @param array   $states Post states.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function display_custom_state( $states, $post ) {
		$status = get_post_status( $post );
		foreach ( $this->statuses as $custom_status ) {
			if ( $custom_status['slug'] === $status ) {
				$states[] = $custom_status['label'];
			}
		}
		return $states;
	}

	/**
	 * Add status filter dropdown.
	 *
	 * @param string $post_type Post type.
	 */
	public function add_status_filter( $post_type ) {
		if ( ! in_array( $post_type, array( 'post', 'page' ), true ) ) {
			return;
		}
		$current = isset( $_GET['yt_post_status'] ) ? sanitize_text_field( $_GET['yt_post_status'] ) : '';
		?>
		<select name="yt_post_status">
			<option value=""><?php esc_html_e( 'All Statuses', 'yt-custom-post-status-manager' ); ?></option>
			<?php foreach ( $this->statuses as $status ) : ?>
				<option value="<?php echo esc_attr( $status['slug'] ); ?>" <?php selected( $current, $status['slug'] ); ?>>
					<?php echo esc_html( $status['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Filter posts by status.
	 *
	 * @param WP_Query $query Query object.
	 */
	public function filter_posts_by_status( $query ) {
		global $pagenow;
		if ( is_admin() && 'edit.php' === $pagenow && isset( $_GET['yt_post_status'] ) && ! empty( $_GET['yt_post_status'] ) ) {
			$query->query_vars['post_status'] = sanitize_text_field( $_GET['yt_post_status'] );
		}
	}

	/**
	 * Plugin activation.
	 */
	public static function activate() {
		$default_statuses = array(
			array(
				'slug'  => 'in-review',
				'label' => __( 'In Review', 'yt-custom-post-status-manager' ),
				'color' => '#3498db',
			),
			array(
				'slug'  => 'needs-edits',
				'label' => __( 'Needs Edits', 'yt-custom-post-status-manager' ),
				'color' => '#e74c3c',
			),
			array(
				'slug'  => 'approved',
				'label' => __( 'Approved', 'yt-custom-post-status-manager' ),
				'color' => '#2ecc71',
			),
		);
		if ( ! get_option( 'yt_cpsm_statuses' ) ) {
			add_option( 'yt_cpsm_statuses', $default_statuses );
		}
	}

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate() {
		// Cleanup if needed.
	}
}

/**
 * Uninstall hook.
 */
function yt_cpsm_uninstall() {
	delete_option( 'yt_cpsm_statuses' );
	wp_cache_flush();
}

// Register hooks.
register_activation_hook( __FILE__, array( 'YT_Custom_Post_Status_Manager', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'YT_Custom_Post_Status_Manager', 'deactivate' ) );
register_uninstall_hook( __FILE__, 'yt_cpsm_uninstall' );

// Initialize plugin.
add_action( 'plugins_loaded', array( 'YT_Custom_Post_Status_Manager', 'get_instance' ) );
