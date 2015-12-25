<?php
function vgwc_get_all_categories()
{
	$terms = get_terms('product_cat' , array('hide_empty' => false, 'hierarchical' => true, 'parent' => 0));
	if(!is_wp_error($terms)) return $terms;
	else return array();
}


function vgwc_get_all_themes()
{
	$plugin_themes_dir 	= str_replace("includes/", "", plugin_dir_path(__FILE__)) . "themes";
	$plugin_themes 		= vgwc_get_all_folders($plugin_themes_dir);
	
	$theme_themes_dir	= get_template_directory() . "/vgwc-themes";
	$theme_themes 		= vgwc_get_all_folders($theme_themes_dir);
	
	$vgwc_themes = array_merge($plugin_themes, $theme_themes);
	asort($vgwc_themes);
	
	return $vgwc_themes;
}


function vgwc_get_all_folders($dir)
{
	$result = array();
	$cdir  	= scandir($dir); 
	
	foreach($cdir as $key => $value)
	{
		if(!in_array($value, array(".", "..")))
		{
			if(is_dir($dir . DIRECTORY_SEPARATOR . $value))
			{
				$result[] = $value;
			}			
		}
	}
	
	return $result;
}


function vgwc_get_theme_path($theme)
{
	$theme_array = array();
	
	$theme_theme_path	= get_template_directory() . "/vgwc-themes/" . $theme;
	$plugin_theme_path	= str_replace("includes/", "", plugin_dir_path(__FILE__)) . "themes/" . $theme;
	
	if(is_dir($theme_theme_path)) {
		$theme_array["url"]	 = get_template_directory_uri() . "/vgwc-themes/" . $theme;
		$theme_array["dir"]  = $theme_theme_path;
		$theme_array["func"] = "vgwc_body_" . $theme;
	}
	else {
		$theme_array["url"]  = vgwc_plugin_url . "themes/" . $theme;
		$theme_array["dir"]  = $plugin_theme_path;
		$theme_array["func"] = "vgwc_body_" . $theme;
	}
	
	return $theme_array;
}