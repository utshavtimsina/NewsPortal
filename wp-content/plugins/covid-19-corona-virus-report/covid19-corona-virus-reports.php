<?php
/**
 * Plugin Name: COVID-19 Corona Virus Report
 * Description: Plugin will display summary about corona virus covid19 of whole world
 * Version: 1.0
 * Author: Kinjal Dalwadi
 * Author URI: https://profiles.wordpress.org/kinjaldalwadi/
 */
 
function CVUPDATES_COVID19_Shortcode( $atts ) {
	ob_start();
	$request = wp_remote_get( 'https://corona.lmao.ninja/countries' );
	if( is_wp_error( $request ) ) {
		return false; 
	}
	$body = wp_remote_retrieve_body( $request );
	$data = json_decode( $body );
	?>
	<table id="covid19"  class="display nowrap" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Country</th>
                            <th bgcolor="#aaa">Total Cases</th>
                            <th bgcolor="#00DDDD">Today Cases</th>
                            <th bgcolor="#696969">Total Deaths</th>
                            <th>Today Deaths</th>
                            <th>Recovered</th>
                            <th>Active</th>
                            <th>Critical</th>
                        </tr>
                    </thead>
                    <tbody>
					<?php
					if( ! empty( $data ) ) {
					$count = 0;
					foreach( $data as $corona_result ){ 
						$count++;?>
                        <tr>
                            <th scope="row"><?php echo $count; ?></th>
                            <td><?php echo $corona_result->country;?></td>
                            <td><?php echo $corona_result->cases; ?></td>
                            <td><?php echo $corona_result->todayCases; ?></td>
                            <td><?php echo $corona_result->deaths; ?></td>
                            <td><?php echo $corona_result->todayDeaths; ?></td>
                            <td><?php echo $corona_result->recovered; ?></td>
                            <td><?php echo $corona_result->active; ?></td>
                            <td><?php echo $corona_result->critical; ?></td>
                        </tr>
                      <?php }
					} ?>
                    </tbody>
                </table>
	<?php
	wp_reset_postdata();
	$reportvariable = ob_get_clean();
    return $reportvariable;
}
add_shortcode( 'CVUPDATES_COVID19_Reports', 'CVUPDATES_COVID19_Shortcode' );

function covid19_updates_scripts() {
	wp_enqueue_script('jquery');
    wp_enqueue_script( 'covid19-jquery-datatables', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js', array(), '', true );
    wp_enqueue_script( 'covid19-datatable-responsive', plugin_dir_url( __FILE__ ) . 'js/dataTables.responsive.min.js', array(), '', true );
	wp_enqueue_style( 'covid19-jqdatatable-style', plugin_dir_url( __FILE__ ) . 'css/jquery.dataTables.min.css', array(), null );
	wp_enqueue_style( 'covid19-jqdatatableresponsive-style', plugin_dir_url( __FILE__ ) . 'css/responsive.dataTables.min.css', array(), null );
  	wp_enqueue_script( 'covid19-custom', plugin_dir_url( __FILE__ ) . 'js/custom.js', array(), '', true );
}
add_action( 'wp_enqueue_scripts', 'covid19_updates_scripts' );
?>