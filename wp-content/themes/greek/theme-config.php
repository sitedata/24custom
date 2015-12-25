<?php

/**
  ReduxFramework Sample Config File
  For full documentation, please visit: https://docs.reduxframework.com
 * */

if (!class_exists('greek_theme_config')) {

    class greek_theme_config {

        public $args        = array();
        public $sections    = array();
        public $theme;
        public $ReduxFramework;

        public function __construct() {

            if (!class_exists('ReduxFramework')) {
                return;
            }

            // This is needed. Bah WordPress bugs.  ;)
            if (true == Redux_Helpers::isTheme(__FILE__)) {
                $this->initSettings();
            } else {
                add_action('plugins_loaded', array($this, 'initSettings'), 10);
            }

        }

        public function initSettings() {

            // Just for demo purposes. Not needed per say.
            $this->theme = wp_get_theme();

            // Set the default arguments
            $this->setArguments();

            // Set a few help tabs so you can see how it's done
            $this->setHelpTabs();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }
            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        /**

          This is a test function that will let you see when the compiler hook occurs.
          It only runs if a field	set with compiler=>true is changed.

         * */
        function compiler_action($options, $css, $changed_values) {
            echo '<h1>The compiler hook has run!</h1>';
            echo "<pre>";
            print_r($changed_values); // Values that have changed since the last save
            echo "</pre>";
        }

        /**

          Custom function for filtering the sections array. Good for child themes to override or add to the sections.
          Simply include this function in the child themes functions.php file.

          NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
          so you must use get_template_directory_uri() if you want to use any of the built in icons

         * */
        function dynamic_section($sections) {
            //$sections = array();
            $sections[] = array(
                'title' => esc_html__('Section via hook', 'redux-framework'),
                'desc' => esc_html__('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework'),
                'icon' => 'el-icon-paper-clip',
                // Leave this as a blank section, no options just some intro text set above.
                'fields' => array()
			);

            return $sections;
        }

        /**

          Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

         * */
        function change_arguments($args) {
            //$args['dev_mode'] = true;

            return $args;
        }

        /**

          Filter hook for filtering the default value of any given field. Very useful in development mode.

         * */
        function change_defaults($defaults) {
            $defaults['str_replace'] = 'Testing filter hook!';

            return $defaults;
        }

        // Remove the demo link and the notice of integrated demo from the redux-framework plugin
        function remove_demo() {

            // Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
            if (class_exists('ReduxFrameworkPlugin')) {
                remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::instance(), 'plugin_metalinks'), null, 2);

                // Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
                remove_action('admin_notices', array(ReduxFrameworkPlugin::instance(), 'admin_notices'));
            }
        }

        public function setSections() {

            /**
              Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
             * */
            // Background Patterns Reader
            $sample_patterns_path   = ReduxFramework::$_dir . '../sample/patterns/';
            $sample_patterns_url    = ReduxFramework::$_url . '../sample/patterns/';
            $sample_patterns        = array();

            if (is_dir($sample_patterns_path)) :

                if ($sample_patterns_dir = opendir($sample_patterns_path)) :
                    $sample_patterns = array();

                    while (($sample_patterns_file = readdir($sample_patterns_dir)) !== false) {

                        if (stristr($sample_patterns_file, '.png') !== false || stristr($sample_patterns_file, '.jpg') !== false) {
                            $name = explode('.', $sample_patterns_file);
                            $name = str_replace('.' . end($name), '', $sample_patterns_file);
                            $sample_patterns[]  = array('alt' => $name, 'img' => $sample_patterns_url . $sample_patterns_file);
                        }
                    }
                endif;
            endif;

            ob_start();

            $ct             = wp_get_theme();
            $this->theme    = $ct;
            $item_name      = $this->theme->get('Name');
            $tags           = $this->theme->Tags;
            $screenshot     = $this->theme->get_screenshot();
            $class          = $screenshot ? 'has-screenshot' : '';

            $customize_title = sprintf(esc_html__('Customize &#8220;%s&#8221;', 'redux-framework'), $this->theme->display('Name'));
            
            ?>
            <div id="current-theme" class="<?php echo esc_attr($class); ?>">
            <?php if ($screenshot) : ?>
                <?php if (current_user_can('edit_theme_options')) : ?>
                        <a href="<?php echo wp_customize_url(); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr($customize_title); ?>">
                            <img src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview', 'greek'); ?>" />
                        </a>
                <?php endif; ?>
                    <img class="hide-if-customize" src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview', 'greek'); ?>" />
                <?php endif; ?>

                <h4><?php echo $this->theme->display('Name'); ?></h4>

                <div>
                    <ul class="theme-info">
                        <li><?php printf(esc_html__('By %s', 'redux-framework'), $this->theme->display('Author')); ?></li>
                        <li><?php printf(esc_html__('Version %s', 'redux-framework'), $this->theme->display('Version')); ?></li>
                        <li><?php echo '<strong>' . esc_html__('Tags', 'redux-framework') . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
                    </ul>
                    <p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
            <?php
            if ($this->theme->parent()) {
                printf(' <p class="howto">' . wp_kses(__('This <a href="%1$s">child theme</a> requires its parent theme, %2$s.','greek'), array('a' => array('href' => array(),'title' => array()))) . '</p>', esc_html__('http://codex.wordpress.org/Child_Themes', 'redux-framework'), $this->theme->parent()->display('Name'));
            }
            ?>

                </div>
            </div>

            <?php
            $item_info = ob_get_contents();

            ob_end_clean();

            $sampleHTML = '';
            if (file_exists(get_template_directory(). '/info-html.html')) {
                Redux_Functions::initWpFilesystem();
                
                global $wp_filesystem;

                $sampleHTML = $wp_filesystem->get_contents(get_template_directory(). '/info-html.html');
            }
	
            // General
            $this->sections[] = array(
                'title'     => esc_html__('General', 'redux-framework'),
                'desc'      => esc_html__('General theme options', 'redux-framework'),
                'icon'      => 'el-icon-cog',
                'fields'    => array(

                    array(
                        'id'        => 'logo_main',
                        'type'      => 'media',
                        'title'     => esc_html__('Logo', 'redux-framework'),
                        'compiler'  => 'true',
                        'mode'      => false,
                        'desc'      => esc_html__('Upload logo here.', 'redux-framework'),
					),
					array(
                        'id'        => 'logo_main2',
                        'type'      => 'media',
                        'title'     => esc_html__('Logo White', 'redux-framework'),
                        'compiler'  => 'true',
                        'mode'      => false,
                        'desc'      => esc_html__('Upload logo here.', 'redux-framework'),
					),
					array(
                        'id'        => 'logo_text',
                        'type'      => 'text',
                        'title'     => esc_html__('Logo Text', 'redux-framework'),
                        'default'   => 'greek'
					),
					array(
                        'id'        => 'logo_erorr',
                        'type'      => 'media',
                        'title'     => esc_html__('Logo for error 404 page', 'redux-framework'),
                        'compiler'  => 'true',
                        'mode'      => false,
					),
					array(
                        'id'        => 'opt-favicon',
                        'type'      => 'media',
                        'title'     => esc_html__('Favicon', 'redux-framework'),
                        'compiler'  => 'true',
                        'mode'      => false,
                        'desc'      => esc_html__('Upload favicon here.', 'redux-framework'),
					),
					
					array(
                        'id'        => 'greek_loading',
                        'type'      => 'switch',
                        'title'     => esc_html__('Show Loading Page', 'redux-framework'),
						'default'   => false,
					),
				),
			);
			// Background
            $this->sections[] = array(
                'title'     => esc_html__('Background', 'redux-framework'),
                'desc'      => esc_html__('Use this section to upload background images, select background color', 'redux-framework'),
                'icon'      => 'el-icon-picture',
                'fields'    => array(
					
					array(
                        'id'        => 'background_opt',
                        'type'      => 'background',
                        'output'    => array('body'),
                        'title'     => esc_html__('Body Background', 'redux-framework'),
                        'subtitle'  => esc_html__('Body background with image, color. Only work with box layout', 'redux-framework'),
						'default'   => '#efefef',
					),
				),
			);
			// Colors
            $this->sections[] = array(
                'title'     => esc_html__('Presets', 'redux-framework'),
                'desc'      => esc_html__('Presets options', 'redux-framework'),
                'icon'      => 'el-icon-tint',
			);
			$this->sections[] = array(
                'title'     	=> esc_html__('Presets1', 'redux-framework'),
                'desc'     		=> esc_html__('Presets1 options', 'redux-framework'),
                'icon'      	=> 'el-icon-tint',
				'subsection' 	=> true,
                'fields'    	=> array(
					array(
                        'id'        	=> 'primary_color',
                        'type'      	=> 'color',
                        'title'     	=> esc_html__('Primary Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for primary color (default: #ec5355).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'   	=> '#ec5355',
                        'validate'  	=> 'color',
					),
					array(
                        'id'        	=> 'rate_color',
                        'type'      	=> 'color',
                        //'output'    	=> array(),
                        'title'     	=> esc_html__('Rating Star Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for star of rating (default: #eeee22).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'  		=> '#eeee22',
                        'validate'  	=> 'color',
					),
				),
			);
			$this->sections[] = array(
                'title'     	=> esc_html__('Presets2', 'redux-framework'),
                'desc'      	=> esc_html__('Presets2 options', 'redux-framework'),
                'icon'      	=> 'el-icon-tint',
				'subsection' 	=> true,
                'fields'    	=> array(
					array(
                        'id'        	=> 'primary2_color',
                        'type'      	=> 'color',
                        'title'     	=> esc_html__('Primary Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for primary color (default: #189f2b).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'   	=> '#189f2b',
                        'validate'  	=> 'color',
					),
					array(
                        'id'        	=> 'rate2_color',
                        'type'      	=> 'color',
                        //'output'    	=> array(),
                        'title'     	=> esc_html__('Rating Star Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for star of rating (default: #eeee22).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'   	=> '#eeee22',
                        'validate'  	=> 'color',
					),
				),
			);
			$this->sections[] = array(
                'title'     	=> esc_html__('Presets3', 'redux-framework'),
                'desc'      	=> esc_html__('Presets3 options', 'redux-framework'),
                'icon'      	=> 'el-icon-tint',
				'subsection' 	=> true,
                'fields'    	=> array(
					array(
                        'id'        	=> 'primary3_color',
                        'type'      	=> 'color',
                        'title'     	=> esc_html__('Primary Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for primary color (default: #c30303).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'   	=> '#c30303',
                        'validate'  	=> 'color',
					),
					array(
                        'id'        	=> 'rate3_color',
                        'type'      	=> 'color',
                        //'output'    	=> array(),
                        'title'     	=> esc_html__('Rating Star Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for star of rating (default: #eeee22).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'   	=> '#eeee22',
                        'validate'  	=> 'color',
					),
				),
			);
			$this->sections[] = array(
                'title'     	=> esc_html__('Presets4', 'redux-framework'),
                'desc'      	=> esc_html__('Presets4 options', 'redux-framework'),
                'icon'      	=> 'el-icon-tint',
				'subsection' 	=> true,
                'fields'    	=> array(
					array(
                        'id'        	=> 'primary4_color',
                        'type'      	=> 'color',
                        'title'     	=> esc_html__('Primary Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for primary color (default: #0bd9a9).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'   	=> '#0bd9a9',
                        'validate'  	=> 'color',
					),
					array(
                        'id'        	=> 'rate4_color',
                        'type'      	=> 'color',
                        //'output'    	=> array(),
                        'title'     	=> esc_html__('Rating Star Color', 'redux-framework'),
                        'subtitle'  	=> esc_html__('Pick a color for star of rating (default: #eeee22).', 'redux-framework'),
						'transparent' 	=> false,
                        'default'   	=> '#eeee22',
                        'validate'  	=> 'color',
					),
				),
			);
			
			//Header
			$this->sections[] = array(
                'title'     => esc_html__('Header', 'redux-framework'),
                'desc'      => esc_html__('Header options', 'redux-framework'),
                'icon'      => 'el-icon-tasks',
                'fields'    => array(
					array(
                        'id'        		=> 'topbar_style',
                        'type'      		=> 'select',
                        'title'     		=> esc_html__('Top bar style', 'redux-framework'),
						'subtitle'     		=> esc_html__('Only for header default', 'redux-framework'),
                        'customizer_only'   => true,

                        'options'   	=> array(
                            'tb-trans' 	=> 'Transparent',
                            'tb-white' 	=> 'White',
							'tb-black' 	=> 'Black',
                    ),
                        'default'   => 'tb-trans'
					),
					array(
                        'id'        => 'mini_cart_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Mini cart title', 'redux-framework'),
                        'default'   => 'Shopping Cart'
					),
					array(
                        'id'        => 'title_mobile_menu',
                        'type'      => 'text',
                        'title'     => esc_html__('Title Mobile Menu', 'redux-framework'),
                        'default'   => 'Menu'
					),
				),
			);
			//Bottom 
			$this->sections[] = array(
				'title'     => esc_html__('Bottom', 'redux-framework'),
				'des'       => esc_html__('Bottom options', 'redux-framework'),
				'icon'      => 'el-icon-cog',
				'fields'    => array(
					array(
						'id'       => 'menu-link',
						'type'     => 'select',
						'data'     => 'menus',
						'title'    => esc_html__('Bottom Menu Link', 'redux-framework'),
						'subtitle' => esc_html__('Select a menu', 'redux-framework'),
					),
				)
			);
			$this->sections[] = array(
				'icon'       => 'el-icon-website',
				'title'      => esc_html__('Social Icons', 'redux-framework'),
				'subsection' => true,
				'fields'     => array(
					array(
                        'id'        => 'follow_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Follow Us title', 'redux-framework'),
                        'default'   => 'Follow Us'
					),
					array(
						'id'       => 'ftsocial_icons',
						'type'     => 'sortable',
						'title'    => esc_html__('Footer social Icons', 'redux-framework'),
						'subtitle' => esc_html__('Enter social links', 'redux-framework'),
						'desc'     => esc_html__('Drag/drop to re-arrange', 'redux-framework'),
						'mode'     => 'text',
						'options'  => array(
							'facebook'    => '',
							'twitter'     => '',
							'instagram'   => '',
							'tumblr'      => '',
							'pinterest'   => '',
							'google-plus' => '',
							'linkedin'    => '',
							'behance'     => '',
							'dribbble'    => '',
							'youtube'     => '',
							'vimeo'       => '',
							'rss'         => '',
						),
						'default' => array(
						    'facebook'    => 'https://www.facebook.com/vinawebsolutions',
							'twitter'     => 'https://twitter.com/vnwebsolutions',
							'instagram'   => 'Instagram',
							'tumblr'      => 'Tumblr',
							'pinterest'   => 'Pinterest',
							'google-plus' => 'https://plus.google.com/+HieuJa/posts',
							'linkedin'    => 'Linkedin',
							'behance'     => 'Behance',
							'dribbble'    => 'Dribbble',
							'youtube'     => 'https://www.youtube.com/user/vinawebsolutions',
							'vimeo'       => 'Vimeo',
							'rss'         => 'RSS',
						),
					),
				)
			);
			
			//Footer
			$this->sections[] = array(
                'title'     => esc_html__('Footer', 'redux-framework'),
                'desc'      => esc_html__('Footer options', 'redux-framework'),
                'icon'      => 'el-icon-cog',
                'fields'    => array(
					array(
                        'id'        => 'copyright_show',
                        'type'      => 'switch',
                        'title'     => esc_html__('Show Copyright', 'redux-framework'),
						'default'   => true,
					),
					array(
                        'id'        => 'copyright-author',
                        'type'      => 'text',
                        'title'     => esc_html__('Copyright Author', 'redux-framework'),
                        'default'   => 'VinaGecko.com'
					),
					array(
                        'id'        => 'copyright-link',
                        'type'      => 'text',
                        'title'     => esc_html__('Copyright Link', 'redux-framework'),
                        'default'   => 'http://vinagecko.com'
					),
					array(
                        'id'        => 'footer_payment',
                        'type'      => 'media',
                        'title'     => esc_html__('Image Payment', 'redux-framework'),
                        'compiler'  => 'true',
                        'mode'      => false,
                        'desc'      => esc_html__('Upload logo here.', 'redux-framework'),
					),
				),
			);
			$this->sections[] = array(
				'icon'       => 'el-icon-website',
				'title'      => esc_html__('Popup Newsletter', 'redux-framework'),
				'subsection' => true,
				'fields'     => array(
					array(
                        'id'        => 'newsletter_show',
                        'type'      => 'switch',
                        'title'     => esc_html__('Show Newsletter', 'redux-framework'),
						'default'   => false,
					),
					array(
                        'id'        => 'newsletter_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Newsletter title', 'redux-framework'),
                        'default'   => 'Get In Touch'
                  ),
					array(
						'id'       => 'newsletter_form',
						'type'     => 'text',
						'title'    => esc_html__('Newsletter form ID', 'redux-framework'),
						'subtitle' => esc_html__('The form ID of MailPoet plugin.', 'redux-framework'),
						'validate' => 'numeric',
						'msg'      => 'Please enter a form ID',
						'default'  => '2'
					),
				)
			);
			
			//Fonts
			$this->sections[] = array(
                'title'     => esc_html__('Fonts', 'redux-framework'),
                'desc'      => esc_html__('Fonts options', 'redux-framework'),
                'icon'      => 'el-icon-font',
                'fields'    => array(

                    array(
                        'id'            	=> 'bodyfont',
                        'type'          	=> 'typography',
                        'title'         	=> esc_html__('Body font', 'redux-framework'),
                        //'compiler'      	=> true,  // Use if you want to hook in your own CSS compiler
                        'google'        	=> true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   	=> true,    // Select a backup non-google font in addition to a google font
                        //'font-style'    	=> false, // Includes font-style and weight. Can use font-style or font-weight to declare
                        //'subsets'       	=> false, // Only appears if google is true and subsets not set to false
                        //'font-size'     	=> false,
                        //'line-height'   	=> false,
                        //'word-spacing'  	=> true,  // Defaults to false
                        //'letter-spacing'	=> true,  // Defaults to false
                        //'color'         	=> false,
                        //'preview'       	=> false, // Disable the previewer
                        'all_styles'    	=> true,    // Enable all Google Font style/weight variations to be added to the page
                        'output'        	=> array('body'), // An array of CSS selectors to apply this font style to dynamically
                        //'compiler'      	=> array('h2.site-description-compiler'), // An array of CSS selectors to apply this font style to dynamically
                        'units'         	=> 'px', // Defaults to px
                        'subtitle'      	=> esc_html__('Main body font.', 'redux-framework'),
                        'default'       	=> array(
                            'color'         => '#909090',
                            'font-weight'   => '400',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'google'        => true,
                            'font-size'     => '13px',
                            'line-height'   => '24px'),
					),
					array(
                        'id'            	=> 'headingfont',
                        'type'          	=> 'typography',
                        'title'         	=> esc_html__('Heading font', 'redux-framework'),
                        //'compiler'      	=> true,  // Use if you want to hook in your own CSS compiler
                        'google'        	=> true,    // Disable google fonts. Won't work if you haven't defined your google api key
                        'font-backup'   	=> true,    // Select a backup non-google font in addition to a google font
                        //'font-style'    	=> false, // Includes font-style and weight. Can use font-style or font-weight to declare
                        //'subsets'       	=> false, // Only appears if google is true and subsets not set to false
                        'font-size'     	=> false,
                        'line-height'   	=> false,
                        //'word-spacing'  	=> true,  // Defaults to false
                        //'letter-spacing'	=> true,  // Defaults to false
                        //'color'         	=> false,
                        //'preview'       	=> false, // Disable the previewer
                        'all_styles'    	=> true,    // Enable all Google Font style/weight variations to be added to the page
                        //'output'        	=> array('h1, h2, h3, h4, h5, h6'), // An array of CSS selectors to apply this font style to dynamically
                        //'compiler'      	=> array('h2.site-description-compiler'), // An array of CSS selectors to apply this font style to dynamically
                        'units'         	=> 'px', // Defaults to px
                        'subtitle'      	=> esc_html__('Heading font.', 'redux-framework'),
                        'default'       	=> array(
                            'color'         => '#909090',
                            'font-weight'   => '400',
                            'font-family'   => 'Arial, Helvetica, sans-serif',
                            'google'        => true,
						),
					),
				),
			);
			
			// Layout
            $this->sections[] = array(
                'title'     => esc_html__('Layout', 'redux-framework'),
                'desc'      => esc_html__('Select page layout: Box or Full Width', 'redux-framework'),
                'icon'      => 'el-icon-align-justify',
                'fields'    => array(
					array(
						'id'       => 'page_layout',
						'type'     => 'select',
						'multi'    => false,
						'title'    => esc_html__('Page Layout', 'redux-framework'),
						'options'  => array(
							'full' => 'Full Width',
							'box'  => 'Box'
						),
						'default'  => 'full'
					),
					array(
                        'id'        => 'preset_option',
                        'type'      => 'select',
                        'title'     => esc_html__('Preset', 'redux-framework'),
						'subtitle'      => esc_html__('Select a preset to quickly apply pre-defined colors and fonts', 'redux-framework'),
                        'options'   => array(
							'1' => 'Preset 1',
                            '2' => 'Preset 2',
							'3' => 'Preset 3',
							'4' => 'Preset 4',
                    ),
                        'default'   => '1'
					),
					/* array(
                        'id'        => 'enable_nlpopup',
                        'type'      => 'switch',
                        'title'     => __('Show Newsletter Popup', 'redux-framework'),
						'subtitle'     => __('Show newsletter popup on first time customer visits site', 'redux-framework'),
						'default'   => true,
					), */
					array(
                        'id'        => 'enable_sswitcher',
                        'type'      => 'switch',
                        'title'     => esc_html__('Show Style Switcher', 'redux-framework'),
						'subtitle'  => esc_html__('The style switcher is only for preview on front-end', 'redux-framework'),
						'default'   => false,
					),
				),
			);
			
			//Brand logos
			$this->sections[] = array(
                'title'     => esc_html__('Brand Logos', 'redux-framework'),
                'desc'      => esc_html__('Upload brand logos and links', 'redux-framework'),
                'icon'      => 'el-icon-cog',
                'fields'    => array(
					array(
						'id'          => 'brand_logos',
						'type'        => 'slides',
						'title'       => esc_html__('Logos', 'redux-framework'),
						'desc'        => esc_html__('Upload logo image and enter logo link.', 'redux-framework'),
						'placeholder' => array(
							'title'           => esc_html__('Title', 'redux-framework'),
							'description'     => esc_html__('Description', 'redux-framework'),
							'url'             => esc_html__('Link', 'redux-framework'),
						),
					),
				),
			);
			
			// Sidebar
			$this->sections[] = array(
                'title'     => esc_html__('Sidebar', 'redux-framework'),
                'desc'      => esc_html__('Sidebar options', 'redux-framework'),
                'icon'      => 'el-icon-cog',
                'fields'    => array(
					array(
						'id'       	=> 'sidebar_pos',
						'type'     	=> 'radio',
						'title'    	=> esc_html__('Main Sidebar Position', 'redux-framework'),
						'subtitle'      => esc_html__('Sidebar on category page', 'redux-framework'),
						'options'  	=> array(
							'left' 	=> 'Left',
							'right' => 'Right'),
						'default'  	=> 'left'
					),
					array(
						'id'       	=> 'sidebar_product',
						'type'     	=> 'radio',
						'title'    	=> esc_html__('Product Sidebar Position', 'redux-framework'),
						'subtitle'      => esc_html__('Sidebar on product page', 'redux-framework'),
						'options'  	=> array(
							'left' 	=> 'Left',
							'right' => 'Right'),
						'default'  	=> 'left'
					),
					array(
						'id'       	=> 'sidebarse_pos',
						'type'     	=> 'radio',
						'title'    	=> esc_html__('Secondary Sidebar Position', 'redux-framework'),
						'subtitle'  => esc_html__('Sidebar on pages', 'redux-framework'),
						'options'  	=> array(
							'left' 	=> 'Left',
							'right' => 'Right'),
						'default'  	=> 'left'
					),
					array(
						'id'       	=> 'sidebarblog_pos',
						'type'     	=> 'radio',
						'title'    	=> esc_html__('Blog Sidebar Position', 'redux-framework'),
						'subtitle'  => esc_html__('Sidebar on Blog pages', 'redux-framework'),
						'options'  	=> array(
							'left' 	=> 'Left',
							'right' => 'Right'),
						'default'  	=> 'right'
					),
				),
			);
			
			// Portfolio
            $this->sections[] = array(
                'title'     => esc_html__('Portfolio', 'redux-framework'),
                'desc'      => esc_html__('Use this section to select options for portfolio', 'redux-framework'),
                'icon'      => 'el-icon-tags',
                'fields'    => array(
					array(
						'id'        	=> 'portfolio_columns',
						'type'      	=> 'slider',
						'title'     	=> esc_html__('Portfolio Columns', 'redux-framework'),
						"default"   	=> 3,
						"min"       	=> 2,
						"step"      	=> 1,
						"max"       	=> 4,
						'display_value' => 'text'
					),
					array(
						'id'        	=> 'portfolio_per_page',
						'type'      	=> 'slider',
						'title'     	=> esc_html__('Projects per page', 'redux-framework'),
						'desc'      	=> esc_html__('Amount of projects per page on portfolio page', 'redux-framework'),
						"default"   	=> 15,
						"min"       	=> 4,
						"step"      	=> 1,
						"max"       	=> 48,
						'display_value' => 'text'
					),
					array(
                        'id'        => 'related_project_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Related projects title', 'redux-framework'),
                        'default'   => 'Related Projects'
					),
				),
			);
		  
			// Product
			$this->sections[] = array(
                'title'     => esc_html__('Product', 'redux-framework'),
                'desc'      => esc_html__('Use this section to select options for product', 'redux-framework'),
                'icon'      => 'el-icon-tags',
                'fields'    => array(
					array(
                        'id'        => 'cat_banner_img',
                        'type'      => 'media',
                        'title'     => esc_html__('Banner Header Category', 'redux-framework'),
                        'compiler'  => 'true',
                        'mode'      => false,
                        'desc'      => esc_html__('Upload banner category here.', 'redux-framework'),
					),
					array(
                        'id'        => 'cat_banner_link',
                        'type'      => 'text',
                        'title'     => esc_html__('Link Banner Category', 'redux-framework'),
                        'default'   => 'http://vinagecko.com'
					),
					array(
                        'id'        => 'shop_header',
                        'type'      => 'background',
                        'output'    => array('.shop_header'),
                        'title'     => esc_html__('Shop category header background', 'redux-framework'),
						'default'   => '#eee',
					),
					array(
                        'id'        => 'product_header',
                        'type'      => 'background',
                        'output'    => array('.product_header'),
                        'title'     => esc_html__('Product page header background', 'redux-framework'),
						'default'   => '#eee',
					),
					array(
						'id'		=>'product_header_code',
						'type' 		=> 'textarea',
						'title' 	=> esc_html__('Product header signup', 'redux-framework'),
						'default' 	=> '',
					),
					array(
						'id'        	=> 'product_per_page',
						'type'      	=> 'slider',
						'title'     	=> esc_html__('Products per page', 'redux-framework'),
						'subtitle'  	=> esc_html__('Amount of products per page on category page', 'redux-framework'),
						"default"   	=> 12,
						"min"       	=> 3,
						"step"      	=> 1,
						"max"       	=> 48,
						'display_value' => 'text'
					),
					/* array(
						'id'        	=> 'shortcode_limit',
						'type'      	=> 'slider',
						'title'     	=> __('Shortcode products limit', 'redux-framework'),
						'desc'      	=> __('Limit number of products by woocommerce shortcode [products]', 'redux-framework'),
						"default"   	=> 10,
						"min"       	=> 4,
						"step"      	=> 1,
						"max"       	=> 48,
						'display_value' => 'text'
					), */
					array(
                        'id'        => 'related_title',
                        'type'      => 'text',
                        'title'     => esc_html__('Related products title', 'redux-framework'),
                        'default'   => 'Related Products'
					),
					array(
						'id'        	=> 'related_amount',
						'type'      	=> 'slider',
						'title'     	=> esc_html__('Number of related products', 'redux-framework'),
						"default"   	=> 6,
						"min"       	=> 3,
						"step"      	=> 1,
						"max"       	=> 16,
						'display_value' => 'text'
					),
					
					array(
						'id'		=>'share_head_code',
						'type' 		=> 'textarea',
						'title' 	=> esc_html__('ShareThis/AddThis head tag', 'redux-framework'), 
						'desc' 		=> esc_html__('Paste your ShareThis or AddThis head tag here', 'redux-framework'),
						'default' 	=> '',
					),
					array(
						'id'		=>'share_code',
						'type' 		=> 'textarea',
						'title' 	=> esc_html__('ShareThis/AddThis code', 'redux-framework'), 
						'desc' 		=> esc_html__('Paste your ShareThis or AddThis code here', 'redux-framework'),
						'default' 	=> ''
					),
				),
			);
			
			// Less Compiler
            $this->sections[] = array(
                'title'     => esc_html__('Less Compiler', 'redux-framework'),
                'desc'      => esc_html__('Turn on this option to apply all theme options. Turn of when you have finished changing theme options and your site is ready.', 'redux-framework'),
                'icon'      => 'el-icon-wrench',
                'fields'    => array(
					array(
                        'id'        => 'enable_less',
                        'type'      => 'switch',
                        'title'     => esc_html__('Enable Less Compiler', 'redux-framework'),
						'default'   => true,
					),
				),
			);
			
            $theme_info  = '<div class="redux-framework-section-desc">';
            $theme_info .= '<p class="redux-framework-theme-data description theme-uri">' . wp_kses(__('<strong>Theme URL:</strong> ', 'redux-framework'), array('strong' => array())) . '<a href="' . $this->theme->get('ThemeURI') . '" target="_blank">' . $this->theme->get('ThemeURI') . '</a></p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-author">' . wp_kses(__('<strong>Author:</strong> ', 'redux-framework'), array('strong' => array())) . $this->theme->get('Author') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-version">' . wp_kses(__('<strong>Version:</strong> ', 'redux-framework'), array('strong' => array())) . $this->theme->get('Version') . '</p>';
            $theme_info .= '<p class="redux-framework-theme-data description theme-description">' . $this->theme->get('Description') . '</p>';
            $tabs 		 = $this->theme->get('Tags');
            if (!empty($tabs)) {
                $theme_info .= '<p class="redux-framework-theme-data description theme-tags">' . wp_kses(__('<strong>Tags:</strong> ', 'redux-framework'), array('strong' => array())) . implode(', ', $tabs) . '</p>';
            }
            $theme_info .= '</div>';

            $this->sections[] = array(
                'icon'              => 'el-icon-list-alt',
                'title'             => esc_html__('Customizer Only', 'redux-framework'),
                'desc'              => wp_kses(__('<p class="description">This Section should be visible only in Customizer</p>', 'redux-framework'), array('p' => array('class' => array()))),
                'customizer_only'   => true,
                'fields'    => array(
                    array(
                        'id'        => 'opt-customizer-only',
                        'type'      => 'select',
                        'title'     => esc_html__('Customizer Only Option', 'redux-framework'),
                        'subtitle'  => esc_html__('The subtitle is NOT visible in customizer', 'redux-framework'),
                        'desc'      => esc_html__('The field desc is NOT visible in customizer.', 'redux-framework'),
                        'customizer_only'   => true,

                        //Must provide key => value pairs for select options
                        'options'   => array(
                            '1' => 'Opt 1',
                            '2' => 'Opt 2',
                            '3' => 'Opt 3'
						),
                        'default'   => '2'
					),
				)
			);            
            
            $this->sections[] = array(
                'title'     => esc_html__('Import / Export', 'redux-framework'),
                'desc'      => esc_html__('Import and Export your Redux Framework settings from file, text or URL.', 'redux-framework'),
                'icon'      => 'el-icon-refresh',
                'fields'    => array(
                    array(
                        'id'            => 'opt-import-export',
                        'type'          => 'import_export',
                        'title'         => 'Import Export',
                        'subtitle'      => 'Save and restore your Redux options',
                        'full_width'    => false,
					),
				),
			);

            $this->sections[] = array(
                'icon'      => 'el-icon-info-sign',
                'title'     => esc_html__('Theme Information', 'redux-framework'),
                //'desc'      => __('<p class="description">This is the Description. Again HTML is allowed</p>', 'redux-framework'),
                'fields'    => array(
                    array(
                        'id'        => 'opt-raw-info',
                        'type'      => 'raw',
                        'content'   => $item_info,
					)
				),
			);
        }

        public function setHelpTabs() {

            // Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-1',
                'title'     => esc_html__('Theme Information 1', 'redux-framework'),
                'content'   => wp_kses(__('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework'), array('p' => array()))
			);

            $this->args['help_tabs'][] = array(
                'id'        => 'redux-help-tab-2',
                'title'     => esc_html__('Theme Information 2', 'redux-framework'),
                'content'   => wp_kses(__('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework'), array('p' => array()))
			);

            // Set the help sidebar
            $this->args['help_sidebar'] = wp_kses(__('<p>This is the sidebar content, HTML is allowed.</p>', 'redux-framework'), array('p' => array()));
        }

        /**

          All the possible arguments for Redux.
          For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

         * */
        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.

            $this->args = array(
                // TYPICAL -> Change these values as you need/desire
                'opt_name'          => 'greek_options',            // This is where your data is stored in the database and also becomes your global variable name.
                'display_name'      => $theme->get('Name'),     // Name that appears at the top of your panel
                'display_version'   => $theme->get('Version'),  // Version that appears at the top of your panel
                'menu_type'         => 'menu',                  //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu'    => true,                    // Show the sections below the admin menu item or not
                'menu_title'        => esc_html__('Theme Options', 'redux-framework'),
                'page_title'        => esc_html__('Theme Options', 'redux-framework'),
                
                // You will need to generate a Google API key to use this feature.
                // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
                'google_api_key' 	=> '', // Must be defined to add google fonts to the typography module
                
                'async_typography'  => true,                    // Use a asynchronous font on the front end or font string
                //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
                'admin_bar'         => true,                    // Show the panel pages on the admin bar
                'global_variable'   => '',                      // Set a different name for your global variable other than the opt_name
                'dev_mode'          => false,                    // Show the time the page took to load, etc
                'customizer'        => true,                    // Enable basic customizer support
                //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
                //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

                // OPTIONAL -> Give you extra features
                'page_priority'     => null,                    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent'       => 'themes.php',            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions'  => 'manage_options',        // Permissions needed to access the options panel.
                'menu_icon'         => '',                      // Specify a custom URL to an icon
                'last_tab'          => '',                      // Force your panel to always open to a specific tab (by id)
                'page_icon'         => 'icon-themes',           // Icon displayed in the admin panel next to your menu_title
                'page_slug'         => '_options',              // Page slug used to denote the panel
                'save_defaults'     => true,                    // On load save the defaults to DB before user clicks save or not
                'default_show'      => false,                   // If true, shows the default value next to each field that is not the default value.
                'default_mark'      => '',                      // What to print by the field's title if the value shown is default. Suggested: *
                'show_import_export' => true,                   // Shows the Import/Export panel when not used as a field.
                
                // CAREFUL -> These options are for advanced use only
                'transient_time'    => 60 * MINUTE_IN_SECONDS,
                'output'            => true,                    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag'        => true,                    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.
                
                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database'          => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'system_info'       => false, // REMOVE

                // HINTS
                'hints' => array(
                    'icon'          => 'icon-question-sign',
                    'icon_position' => 'right',
                    'icon_color'    => 'lightgray',
                    'icon_size'     => 'normal',
                    'tip_style'     => array(
                        'color'         => 'light',
                        'shadow'        => true,
                        'rounded'       => false,
                        'style'         => '',
					),
                    'tip_position'  => array(
                        'my' => 'top left',
                        'at' => 'bottom right',
					),
                    'tip_effect'    => array(
                        'show'          => array(
                            'effect'        => 'slide',
                            'duration'      => '500',
                            'event'         => 'mouseover',
						),
                        'hide'      => array(
                            'effect'    => 'slide',
                            'duration'  => '500',
                            'event'     => 'click mouseleave',
						),
					),
				)
			);


            // SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
            $this->args['share_icons'][] = array(
                'url'   => 'https://github.com/ReduxFramework/ReduxFramework',
                'title' => 'Visit us on GitHub',
                'icon'  => 'el-icon-github'
                //'img'   => '', // You can use icon OR img. IMG needs to be a full URL.
			);
            $this->args['share_icons'][] = array(
                'url'   => 'https://www.facebook.com/pages/Redux-Framework/243141545850368',
                'title' => 'Like us on Facebook',
                'icon'  => 'el-icon-facebook'
			);
            $this->args['share_icons'][] = array(
                'url'   => 'http://twitter.com/reduxframework',
                'title' => 'Follow us on Twitter',
                'icon'  => 'el-icon-twitter'
			);
            $this->args['share_icons'][] = array(
                'url'   => 'http://www.linkedin.com/company/redux-framework',
                'title' => 'Find us on LinkedIn',
                'icon'  => 'el-icon-linkedin'
			);

            // Panel Intro text -> before the form
            if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false) {
                if (!empty($this->args['global_variable'])) {
                    $v = $this->args['global_variable'];
                } else {
                    $v = str_replace('-', '_', $this->args['opt_name']);
                }
                //$this->args['intro_text'] = sprintf(__('<p>Did you know that Redux sets a global variable for you? To access any of your saved options from within your code you can use your global variable: <strong>$%1$s</strong></p>', 'redux-framework'), $v);
            } else {
                //$this->args['intro_text'] = __('<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'redux-framework');
            }

            // Add content after the form.
            //$this->args['footer_text'] = __('<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', 'redux-framework');
        }

    }
    
    global $reduxConfig;
    $reduxConfig = new greek_theme_config();
}

/**
  Custom function for the callback referenced above
 */
if (!function_exists('redux_my_custom_field')):
    function redux_my_custom_field($field, $value) {
        print_r($field);
        echo '<br/>';
        print_r($value);
    }
endif;

/**
  Custom function for the callback validation referenced above
 * */
if (!function_exists('redux_validate_callback_function')):
    function redux_validate_callback_function($field, $value, $existing_value) {
        $error = false;
        $value = 'just testing';

        /*
          do your validation

          if(something) {
            $value = $value;
          } elseif(something else) {
            $error = true;
            $value = $existing_value;
            $field['msg'] = 'your custom error message';
          }
         */

        $return['value'] = $value;
        if ($error == true) {
            $return['error'] = $field;
        }
        return $return;
    }
endif;
