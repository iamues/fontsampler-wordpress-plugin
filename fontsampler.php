<?php
/*
Plugin Name: Fontsampler
Plugin URI:  http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Create editable webfont previews via shortcodes
Version:     0.0.1
Author:      Johannes Neumeier
Author URI:  http://johannesneumeier.com
License:
License URI:
Text Domain: fontsampler
*/
defined('ABSPATH') or die('Nope.');

global $wpdb;
$f = new Fontsampler($wpdb);

// frontend
wp_register_script( 'fontsampler-js', plugin_dir_url(__FILE__) . 'js/jquery.fontsampler.js', array( 'jquery' ));
wp_register_script( 'fontsampler-init-js', plugin_dir_url(__FILE__) . 'js/fontsampler-init.js', array( 'fontsampler-js'));
wp_enqueue_style( 'fontsampler-css', plugin_dir_url(__FILE__) . 'fontsampler-interface.css');
add_shortcode( 'fontsampler', array($f, 'fontsampler_shortcode'));

// backend
add_action('admin_menu', array($f, 'fontsampler_plugin_setup_menu'));
add_action('admin_enqueue_scripts', array($f, 'fontsampler_admin_enqueues'));
add_filter('upload_mimes', array($f, 'allow_font_upload_types'));
register_activation_hook( __FILE__, array($f, 'fontsampler_activate'));



class Fontsampler {

	private $db;
    private $table_sets;
    private $table_fonts;

	function Fontsampler ($wpdb) {
		$this->db = $wpdb;
        $this->table_sets = $this->db->prefix . "fontsampler_sets";
        $this->table_fonts = $this->db->prefix . "fontsampler_fonts";
	}


	/*
	 * DIFFERENT HOOKS
	 */


	/*
	 * Register the [fontsampler id=XX] hook for use in pages and posts
	 */
	function fontsampler_shortcode( $atts ) {
		wp_enqueue_script( 'fontsampler-js' );
		wp_enqueue_script( 'fontsampler-init-js' );

		// merge in possibly passed in attributes
		$attributes = shortcode_atts( array('id' => '0'), $atts);
		// do nothing for missing id
		if ($attributes['id'] != 0) {
			$font = $this->get_font($attributes['id']);
			$interface = file_get_contents(plugin_dir_url(__FILE__) . 'interface.html');

			$search = array(
				'[font-size-label]',
				'[font-size-min]',
				'[font-size-max]',
				'[font-size-value]',
				'[font-size-unit]',
				'[letter-spacing-label]',
				'[letter-spacing-min]',
				'[letter-spacing-max]',
				'[letter-spacing-value]',
				'[letter-spacing-unit]',
				'[line-height-label]',
				'[line-height-min]',
				'[line-height-max]',
				'[line-height-value]',
				'[line-height-unit]'
			);

			$replace = array(
				'Size',
				'8',
				'96',
				'14',
				'px',
				'Letter spacing',
				'-5',
				'5',
				'0',
				'px',
				'Line height',
				'70',
				'300',
				'110',
				'%'
			);

			$interface = str_replace($search, $replace, $interface);

			return '<div class="fontsampler-wrapper">' . $interface . '<div class="fontsampler" data-fontfile="' . $font['guid'] . '">FONTSAMPLER</div></div>';
		}
	}


    /*
     * Register scripts and styles needed in the admin panel
     */
    function fontsampler_admin_enqueues() {
        wp_register_style( 'fontsampler_admin_css', plugin_dir_url(__FILE__) . '/fontsampler-admin.css', false, '1.0.0' );
        wp_enqueue_style( 'fontsampler_admin_css' );
    }


	/*
	 * Add the fontsampler admin menu to the sidebar
	 */
	function fontsampler_plugin_setup_menu() {
        add_menu_page( 'Fontsampler plugin page', 'Fontsampler Plugin', 'manage_options', 'fontsampler', array($this, 'fontsampler_admin_init') );
	}


	/*
	 * Expand allowed upload types to include font files
	 */
	function allow_font_upload_types($existing_mimes=array()){
		$existing_mimes['woff'] = 'application/font-woff';
		$existing_mimes['woff2'] = 'application/font-woff2';
		return $existing_mimes;
	}


	/*
	 * React to the plugin being activated
	 */
	function fontsampler_activate() {
		$this->create_table();
	}

	// TODO deactivate()


	/*
	 * FLOW CONTROL
	 */

	function fontsampler_admin_init() {

        echo '<section id="fontsampler-admin">';

        include('includes/header.php');

        // check the fontsampler tables exist, and if not, create it now
        $this->db->query("SHOW TABLES LIKE '" . $this->table_sets . "'");
        if ($this->db->num_rows === 0) {
            $this->create_table_sets();
        }
        $this->db->query("SHOW TABLES LIKE '" . $this->table_fonts . "'");
        if ($this->db->num_rows === 0) {
            $this->create_table_fonts();
        }


        // check upload folder is writable
        $dir = wp_upload_dir();
        $upload = $dir['basedir'];

        if (!is_dir($upload)) {
            echo '<p>Uploads folder does not exist! Make sure Wordpress has writing permissions to create the
                    uploads folder at: <em>' . $upload . '</em></p>';
        }


		$this->handle_font_edit();
		$this->handle_set_edit();
		$this->handle_set_delete();

		switch ($_GET['subpage']) {
            case 'create':
                $set = NULL;
                $fonts = $this->get_fonts();
                include('includes/edit.php');
                break;

            case 'edit':
                $set = $this->get_set(intval($_GET['id']));
                $fonts = $this->get_fonts();
                include('includes/edit.php');
                break;
/*
            case 'delete':
                include('includes/delete.php');
                break;
*/
            case 'fonts':
                $fonts = $this->get_fonts();
                include('includes/fonts.php');
                break;

            case 'font_create':
                $font = NULL;
                include('includes/font-edit.php');
                break;

            case 'font_edit':
                $font = $this->get_font(1);
                include('includes/font-edit.php');
                break;

			default:
				$sets = $this->get_sets();
				include('includes/sets.php');
			    break;
		}
        echo '</section>';
	}


	/*
	 * DATABASE INTERACTION
	 */

	/*
	 * setup fontsampler sets table
	 */
	function create_table_sets() {
		$sql = "CREATE TABLE " . $this->table_sets . " (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `upload_id` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				)";
		$this->db->query($sql);
	}

    function create_table_fonts() {
    	$sql = "CREATE TABLE " . $this->table_fonts . " (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `woff` int(11) unsigned DEFAULT NULL,
			  `woff2` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`id`)
			)";
		$this->db->query($sql);
    }


	/*
	 * Read from fontsampler sets table
	 */
	function get_sets() {
		$sql = "SELECT s.id, f.name FROM " . $this->table_sets . " s
				LEFT JOIN " . $this->table_fonts . " f
				ON s.font_id = f.id";
		return $this->db->get_results($sql, ARRAY_A);
	}


	function get_set($id) {
		$sql = "SELECT * FROM " . $this->table_sets . " s
				LEFT JOIN " . $this->table_fonts . " f
				ON s.font_id = f.id
				WHERE s.id = " . $id;
		return $this->db->get_row($sql, ARRAY_A);
	}


	/*
	 * Remove a fontsampler set
	 */
	function delete_set($id) {
		return $this->db->delete($this->table_sets, array('id' => $id));
	}


	/*
	 * read from attachements
	 */
	function get_fonts() {
		$sql = "SELECT *,
                    (SELECT guid FROM " . $this->db->prefix . "posts WHERE ID = " . $this->table_fonts . ".woff) AS woff_file,
                    (SELECT guid FROM " . $this->db->prefix . "posts WHERE ID = " . $this->table_fonts . ".woff2) AS woff2_file
                FROM " . $this->table_fonts;
        return $this->db->get_results($sql, ARRAY_A);
	}


	/*
	 * read per id from custom table
	 */
	function get_font($id) {
		$sql = "SELECT p.post_name, p.guid FROM " . $this->table_sets . " f
				LEFT JOIN " . $this->db->prefix . "posts p
				ON f.upload_id = p.ID";
		return $this->db->get_row($sql, ARRAY_A);
	}


	/*
	 * PROCESSING FROMS
	 */

	/*
	 * Dealing with new fonts being defined and uploaded explicitly via the plugin (instead of the media gallery)
	 */
	function handle_font_edit() {
        if ($_POST['action'] == 'font-edit' && !empty($_POST['fontname'])) {

            echo '<div class="notice">';

            $data = array('name' => $_POST['fontname']);

            $formatName = array('woff' => 'fontfile_woff', 'woff2' => 'fontfile_woff2');

            foreach ($formatName as $label => $name) {
                if (isset($_FILES[$name]) && $_FILES[$name]['size'] > 0) {
                    $uploaded = media_handle_upload($name, 0);
                    if (is_wp_error($uploaded)) {
                        $this->error('Error uploading ' . $label . ' file: ' . $uploaded->get_error_message());
                    } else {
                        $this->info('Uploaded ' . $label . ' file: '. $_FILES[$name]['name']);
                        $data[$label] = $uploaded;
                    }
                } else {
                    $this->notice('No ' . $label . ' file provided. You can still add it later.');
                }
            }

            if ($_POST['id'] == 0) {
                $res = $this->db->insert($this->table_fonts, $data);
            } else {
                $this->db->update($this->table_fonts, $data, array('ID' => $_POST['id']));
            }

            echo '</div>';
        }
	}

	/*
	 * Creating a fontsampler 
	 */
	function handle_set_edit() {
		if ($_POST['action'] == "editSet") {
			if (!isset($_POST['id'])) {
				// insert new
				$this->db->insert($this->table_sets, array('font_id' => intval($_POST['font_id'])));
				echo "Created set with it " . $this->db->insert_id;
			} else {
				// update existing
			}
		}
	}

	function handle_set_delete() {
		$id = (int)($_POST['id']);
		if ($_POST['action'] == 'deleteSet' && isset($id) && is_int($id) && $id > 0) {
			if ($this->delete_set(intval($_POST['id']))) {
				echo 'Deleted ' . $id;
			}
		}
	}


    /*
     * HELPERS
     */

    /*
     * Render different confirmation messages
     */
    function info($message) {
        echo '<strong class="info">' . $message . '</strong>';
    }
    function notice($message) {
        echo '<strong class="note">' . $message . '</strong>';
    }
    function error($message) {
        echo '<strong class="error">' . $message . '</strong>';
    }

}
?>