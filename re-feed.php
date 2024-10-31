<?php

/* Plugin Name: RE Feed by CustomScripts
Plugin URI: https://wordpress.org/plugins/re-feed
Description: Sync real estate listings data with Zillow via a custom ZIF (Zillow Interchange Format) feed. The Listings post type requires RE Lister.
Version: 1.1
Author: CustomScripts
Author URI: https://customscripts.tech

Copyright 2009-2017  Christopher Buck  (email : support@customscripts.tech)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//==========================================================//
/*    DEPENDENCIES    */
//==========================================================//

/* Require the dependent plugin, RE-Lister (wordpress.org/plugins/re-lister) */

require_once dirname( __FILE__ ) . '/assets/php/tgm-plugin-activation/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'cst_ref_register_required_plugins' );

function cst_ref_register_required_plugins() {
    $plugins = array(
        array(
        'name' => 'RE Lister',
        'slug' => 're-lister',
        'required' => true
        ),
    );
    
    $config = array(
		'id'           => 're-feed',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'plugins.php',            // Parent menu slug.
		'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => true,                   // Automatically activate plugins after installation or not.
		'message'      => '',  
    );
    
    tgmpa( $plugins, $config );
}

//==========================================================//
/*    CONSTRUCT THE FEED    */
//==========================================================//

/* Main function call to kick off all subsequent callback functions to create and display the feed */
function cst_ref_custom_feed(){
    //Add the feed slug and constructor callback
    add_feed('zillow-listings', 'cst_ref_create_feed');
}
add_action('init', 'cst_ref_custom_feed');

/**
 * Setting a new cache time for feeds in WordPress
 */
function prefix_set_feed_cache_time( $seconds ) {
	return 10;
}
add_filter( 'wp_feed_cache_transient_lifetime' , 'prefix_set_feed_cache_time' );
function cst_ref_get_meta_field( $id, $section, $field_name ){
    $output = '<SampleData>Hello, This is sample data</SampleData>';
    /*$pref = '_cst_rel_meta_';
    $parent = strtolower( str_replace( " ", "", $section )) . '_';
    $field_label = strtolower( str_replace( " ", "_", $field_name ) );
    $meta_key = $pref . $parent . $field_label;
    //if( get_post_meta( $id, $meta_key, true ) != null ){
        $output = '<' . $field_name . '>';
        $output .= get_post_meta( $id, $meta_key, true);
        $output .= '</' . $field_name . '>';
    //}*/
    echo $output;
}

/* Callback (from within the loop) to check for custom post type and status */
function cst_ref_check_post_status( $id ){
    $post_bool = false;
    if( get_post_status( $id ) == 'publish' && get_post_type( $id ) == 'listings' ){
        $post_bool = true;
    }
    return $post_bool;
}

/* Callback to construct feed layout */
function cst_ref_create_feed(){
    
    /* Helper to detect illegal characters and wrap in CDATA tag */
    function cst_ref_detect_illegal_chars( $txt ){
        $found = false;
        $illegals = array(
            chr(34),    //Quote
            chr(38),    //Ampersand
            chr(44),    //Apostrophe
            chr(60),    //Less Than
            chr(62),    //Greater Than
            'http://',  //HTTP
            'https://', //HTTPS
        );
        foreach ( $illegals as $val ){
            if( strpos( $txt, $val) == false ){
                //$found = false;   
            } else {
                $found = true;
            }
        }
        if( $found == true ){
            return '<![CDATA[' . $txt . ']]>';
        } else {
            return $txt;
        }
    }
    
    /* Helper to get post meta value */
    function cst_ref_get_post_meta( $id, $parent, $field ){
        $pref = '_cst_rel_meta_';
        $parent_field = strtolower( str_replace( " ", "", $parent ) );
        $field_name = strtolower( str_replace( " ", "_", $field) );
        $meta_key = $pref . $parent_field . '_'. $field_name;
        $open_lbl = '<' . str_replace( " ", "", $field ) . '>';
        $close_lbl = '</' . str_replace( " ", "", $field ) . '>';
        if ( strlen( get_post_meta( $id, $meta_key, true) ) > 0 ){
            $val = cst_ref_detect_illegal_chars( str_replace( "checked", "Yes", get_post_meta($id, $meta_key, true) ) );
            echo $open_lbl;
            echo $val;
            echo $close_lbl;
        }
    }
    
    /* Helper to get subsection meta values */
    function cst_ref_get_sub_meta( $id, $section, $arr ){
        $pref = '_cst_rel_meta_';
        $parent_field = strtolower( str_replace( " ", "", $section ) );
        $display = false;
        foreach ( $arr as $v ){
            $tempkey = strtolower( str_replace( " ", "_", $v ) );
            $tempval = get_post_meta( $id, $pref . $parent_field . '_' . $tempkey, true );
            if ( strlen($tempval) > 0){
                $display = true;
            }
        }
        if ( $display == true ){
            $open_lbl = '<' . str_replace( " ", "", $section ) . '>';
            $close_lbl = '</' . str_replace( " ", "", $section ) . '>';
            echo $open_lbl;
            foreach( $arr as $v ){
                $field_key = strtolower( str_replace( " ", "_", $v ) );
                $field_open = '<' . str_replace( " ", "", $v ) . '>';
                $val = cst_ref_detect_illegal_chars( str_replace( "checked", "Yes", get_post_meta( $id, $pref . $parent_field . '_' . $field_key, true ) ) );
                $field_close = '</' . str_replace( " ", "", $v ) . '>';
                if( strlen($val) > 0 ){
                    echo $field_open;
                    echo $val;
                    echo $field_close;
                }
            }
            echo $close_lbl;
        }
    }
    
$postCount = 10; // The number of posts to show in the feed
$posts = query_posts('showposts=' . $postCount);
header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>
<Listings>
<?php
    $args = array(
        'post_type' => 'listings',
    );
    $query = new WP_Query( $args ); ?>

    <?php while ( $query->have_posts() ) : $query->the_post(); 
    $id = get_the_ID();
    ?>
    <Listing>
        <Location>
            <?php
                $loc_arr = array('Street Address', 'Unit Number', 'City', 'State', 'Zip', 'Lat', 'Long', 'Display Address');
                foreach ( $loc_arr as $v ){
                    cst_ref_get_post_meta( $id, 'Location', $v );
                }
            ?>
        </Location>
        <ListingDetails>
            <?php
                $details_arr = array('Status', 'Price', 'Listing Url', 'Mls Id', 'Mls Name', 'Provider Listing Id', 'Virtual Tour Url', 'Listing Email', 'Always Email Agent', 'Short Sale', 'REO');
                foreach ( $details_arr as $v ){
                    cst_ref_get_post_meta( $id, 'Listing Details', $v );
                }
            ?>
        </ListingDetails>
        <?php
            if( strlen( get_post_meta( $id, '_cst_rel_meta_rentaldetails_deposit_fees', true ) ) > 0 ){
            ?>
                <RentalDetails>
                    <?php
                        $rental_arr = array( 'Availability', 'Lease Term', 'Deposit Fees' );
                        foreach ( $rental_arr as $v ){
                            cst_ref_get_post_meta( $id, 'Rental Details', $v );
                        }
                        //Utilities Included
                        $util_arr = array( 'Water', 'Sewage', 'Garbage', 'Electricity', 'Gas', 'Internet', 'Cable', 'SatTv' );
                        cst_ref_get_sub_meta( $id, 'Utilities Included', $util_arr);

                        //Pets Allowed
                        $pets_arr = array( 'No Pets', 'Cats', 'Small Dogs', 'Large Dogs' );
                        cst_ref_get_sub_meta( $id, 'Pets Allowed', $pets_arr );
                    ?>
                </RentalDetails>
            <?php
            }
                //Basic Details
                $basic_arr = array('Property Type', 'Title', 'Description', 'Bedrooms', 'Bathrooms', 'Full Bathrooms', 'Half Bathrooms', 'Quarter Bathrooms', 'Three Quarter Bathrooms', 'Living Area', 'Lot Size', 'Year Built' );
                cst_ref_get_sub_meta( $id, 'Basic Details', $basic_arr );
            ?>
        <?php
            if( strlen( get_post_meta( $id, '_cst_rel_meta_picture_picture_url', true ) ) > 0 ){
            ?>
                <Pictures>
                <?php
                    $pic_arr = array('Picture Url', 'Caption');
                    cst_ref_get_sub_meta( $id, 'Picture', $pic_arr );
                ?>
                </Pictures>
        <?php
            }
        ?>
                <Agent>
                    <?php
                        $agent_arr_1 = array( 'First Name', 'Last Name' );
                        foreach( $agent_arr_1 as $v ){
                            cst_ref_get_post_meta( $id, 'Agent', $v );
                        }
                    ?>
                    <EmailAddress>
                    <?php
                        if( strlen( get_post_meta( $id, '_cst_rel_meta_agent_email_address', true ) ) > 0 ){
                            echo get_post_meta( $id, '_cst_rel_meta_agent_email_address', true );
                        }
                    ?>
                    </EmailAddress>
                    <?php
                        $agent_arr_2 = array( 'Picture Url', 'Office Line Number', 'Mobile Phone Line Number', 'Fax Line Number', 'License Num' );
                        foreach( $agent_arr_2 as $v ){
                            cst_ref_get_post_meta( $id, 'Agent', $v );
                        }
                    ?>
                </Agent>
                <Office>
                    <?php
                        $office_arr_1 = array( 'Brokerage Name' );
                        foreach( $office_arr_1 as $v ){
                            cst_ref_get_post_meta( $id, 'Office', $v );
                        }
                    ?>
                    <BrokerPhone>
                    <?php
                        if( strlen( get_post_meta( $id, '_cst_rel_meta_office_broker_phone', true ) ) > 0 ){
                            echo get_post_meta( $id, '_cst_rel_meta_office_broker_phone', true );
                        }
                    ?>
                    </BrokerPhone>
                    <?php
                        $office_arr_2 = array( 'Broker Email', 'Broker Website', 'Street Address', 'Unit Number', 'City', 'State', 'Zip', 'Office Name', 'Franchise Name' );
                        foreach( $office_arr_2 as $v ){
                            cst_ref_get_post_meta( $id, 'Office', $v );
                        }
                    ?>
                </Office>
                <OpenHouses>
                    <?php
                        $open_house_arr = array( 'Date', 'Start Time', 'End Time' );
                        cst_ref_get_sub_meta( $id, 'Open House', $open_house_arr );
                    ?>
                </OpenHouses>
                <Fees>
                    <?php
                        $fees_arr = array( 'Fee Type', 'Fee Amount', 'Fee Period' );
                        cst_ref_get_sub_meta( $id, 'Fee', $fees_arr );
                    ?>
                </Fees>
                <Schools>
                    <?php
                        $schools_arr = array( 'District', 'Elementary', 'Middle', 'High' );
                        foreach( $schools_arr as $v ){
                            cst_ref_get_post_meta( $id, 'Schools', $v );
                        }
                    ?>
                </Schools>
                <Neighborhood>
                    <?php
                        $neighbor_arr = array( 'Name', 'Description' );
                        foreach( $neighbor_arr as $v ){
                            cst_ref_get_post_meta( $id, 'Neighborhood', $v );
                        }
                    ?>
                </Neighborhood>
                <RichDetails>
                    <?php
                        $rich_arr_1 = array( 'Additional Features' );
                        foreach( $rich_arr_1 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
                        $rich_arr_2 = array( 'Appliance' );
                        cst_ref_get_sub_meta( $id, 'Appliances', $rich_arr_2 );
                        $rich_arr_3 = array( 'Architecture Style', 'Attic', 'Barbecue Area', 'Basement', 'Building Unit Count', 'Cable Ready', 'Ceiling Fan', 'Condo Floor Num' );
                        foreach( $rich_arr_3 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
                        
                        $rich_arr_4 = array( 'Cooling System' );
                        cst_ref_get_sub_meta( $id, 'Cooling Systems', $rich_arr_4 );
                        
                        
                        $rich_arr_5 = array( 'Deck', 'Disabled Access', 'Dock', 'Doorman', 'Double Pane Windows', 'Elevator' );
                        foreach( $rich_arr_5 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
                        
                        $rich_arr_6 = array( 'Exterior Type' );
                        cst_ref_get_sub_meta( $id, 'Exterior Types', $rich_arr_6 );
                        
                        $rich_arr_7 = array( 'Fireplace' );
                        foreach( $rich_arr_7 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
                        
                        $rich_arr_8 = array( 'Floor Covering' );
                        cst_ref_get_sub_meta( $id, 'Floor Coverings', $rich_arr_8 );
                    
                        $rich_arr_9 = array( 'Garden', 'Gated Entry', 'Greenhouse' );
                        foreach( $rich_arr_9 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
    
                        $rich_arr_10 = array( 'Heating Fuel' );
                        cst_ref_get_sub_meta( $id, 'Heating Fuels', $rich_arr_10 );
    
                        $rich_arr_11 = array( 'Heating System' );
                        cst_ref_get_sub_meta( $id, 'Heating Systems', $rich_arr_11 );
    
                        $rich_arr_12 = array( 'Hottub Spa', 'Intercom', 'Jetted Bath Tub', 'Lawn', 'Mother In Law', 'Num Floors', 'Num Parking Spaces' );
                        foreach( $rich_arr_12 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
    
                        $rich_arr_13 = array( 'Parking Type' );
                        cst_ref_get_sub_meta( $id, 'Parking Types', $rich_arr_13 );
    
                        $rich_arr_14 = array( 'Patio', 'Pond', 'Pool', 'Porch' );
                        foreach( $rich_arr_14 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
    
                        $rich_arr_15 = array( 'Roof Type' );
                        cst_ref_get_sub_meta( $id, 'Roof Types', $rich_arr_15 );
    
                        $rich_arr_16 = array( 'Room Count' );
                        foreach( $rich_arr_16 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
    
                        $rich_arr_17 = array( 'Room' );
                        cst_ref_get_sub_meta( $id, 'Rooms', $rich_arr_17 );
    
                        $rich_arr_18 = array( 'Rv Parking', 'Sauna', 'Security System', 'Skylight', 'Sports Court', 'Sprinkler System', 'Vaulted Ceiling', 'Fitness Center', 'Basketball Court', 'Tennis Court', 'Near Transportation', 'Controlled Access', 'Over 55 Active Community', 'Assisted Living Community', 'Storage', 'Fenced Yard', 'Property Name', 'Furnished', 'Highspeed Internet', 'Onsite Laundry', 'Cable SatTV' );
                        foreach( $rich_arr_18 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
    
                        $rich_arr_19 = array( 'View Type' );
                        cst_ref_get_sub_meta( $id, 'View Types', $rich_arr_19 );
    
                        $rich_arr_20 = array( 'Waterfront', 'Wetbar', 'What Owner Loves', 'Wired', 'Year Updated' );
                        foreach( $rich_arr_20 as $v ){
                            cst_ref_get_post_meta( $id, 'Rich Details', $v );
                        }
    

    
                    ?>
                </RichDetails>
<?php
        ?>
    </Listing>
    <?php endwhile; ?>
</Listings>
<?php
}