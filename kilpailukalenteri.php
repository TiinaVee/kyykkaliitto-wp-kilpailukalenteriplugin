<?php
/**
Plugin Name: Kilpailukalenteri API
description: Kilpailukalenterin API
Version: 1.0
Author: Tiina Viitanen
License: GPLv2 or later
Text Domain: kilpailukalenteri
*/



defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once 'vendor/autoload.php';
use GuzzleHttp\Client;

add_action('init', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_register_style( 'namespace', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
    wp_enqueue_style( 'namespace' );
}

function hae_kilpailukalenteri_func( $atts )
{	
		
	$a = shortcode_atts( array(
        'vuosi' => 'something'
    ), $atts );
	
	if ($a['vuosi'] != null)
	{
		try 
		{	
			$client = new Client([
			// Base URI is used with relative requests
			'base_uri' => 'http://kyykkaliittoapi.azurewebsites.net/kilpailukalenteri/',
			// You can set any number of default request options.
			'timeout'  => 5.0,
			]);
	
			$response = $client->request('GET', $a['vuosi']);

		if (isset($_GET["Result"]))
		{
			$result = $_GET["Result"];
			if ($result == '1')
			{	
				echo '<div class="alert alert-success"><strong>Ilmoittautuminen onnistui</strong></div>';
			}
			else 
			{
				echo '<div class="alert alert-danger"><strong>Ilmoittautuminen epäonnistui</strong><br/>' . $_GET["Reason"] . '</div>';
			}
		}		

		echo "<h4>Merkkien selitykset</h4>";
		echo "<p>";
		echo '<i class="fa fa-circle" style="color:red;"></i> Henkilökohtainen CUP
		<i class="fa fa-circle" style="color:yellow;"></i> Joukkue-CUP
		<i class="fa fa-circle" style="color:#FA58F4;"></i> Naisten Pari-CUP
		<i class="fa fa-circle" style="color:#0080FF;"></i> Miesten Pari-CUP
		<i class="fa fa-circle" style="color:#A9BCF5;"></i> 5-Ottelu
		<br/>
		<i class="fa fa-circle" style="color:#01DF74;"></i> SM-kilpailut
		<i class="fa fa-circle" style="color:#0000FF;"></i> Halli-SM-kilpailut
		<i class="fa fa-circle" style="color:#AEB404;"></i> Maaottelu
		<i class="fa fa-circle" style="color:pink;"></i> EM-kilpailut
		<i class="fa fa-circle" style="color:grey;"></i> PM-kilpailut
		<i class="fa fa-circle" style="color:blue;"></i> MM-kilpailut';
		echo "</p>";
		
		$body = $response->getBody();
		$kilpailut = json_decode($body, true);
		
		//print_r($kilpailut);
		
		if ($kilpailut != null)
		{
			foreach($kilpailut as $kilpailu)
			{	
				echo '<div class="panel">';
				$showday = strtotime($kilpailu["KilpailuPvm"]);	
				//echo $showday;				
				echo "<h3>" . date("d.m.Y", $showday);
				echo " {$kilpailu["Nimi"]}, {$kilpailu["Paikkakunta"]} ";
				if ($kilpailu["HenkilokohtainenCupKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="Henkilökohtainen CUP" style="color:red;"></i> ';
												
				if ($kilpailu["JoukkueCupKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="Joukkue-CUP" style="color:yellow;"></i> ';
				
				if ($kilpailu["NaistenPariCupKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="Naisten Pari-CUP" style="color:#FA58F4;"></i> ';
				
				if ($kilpailu["MiestenPariCupKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="Miesten Pari-CUP" style="color:#0080FF;"></i> ';
				
				if ($kilpailu["ViisiOttelu"] == 1)
					echo '<i class="fa fa-circle" title="5-ottelu" style="color:#A9BCF5;"></i> ';
				
				if ($kilpailu["SMKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="SM-kilpailut" style="color:#01DF74;"></i> ';
				
				if ($kilpailu["HalliSMKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="Halli-SM-kilpailut" style="color:#0000FF;"></i> ';
				
				if ($kilpailu["Maaottelu"] == 1)
					echo '<i class="fa fa-circle" title="Maaottelu" style="color:#AEB404;"></i> ';
				
				if ($kilpailu["PMKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="PM-kilpailut" style="color:grey;"></i> ';
				
				if ($kilpailu["EMKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="EM-kilpailut" style="color:pink;"></i> ';
				
				if ($kilpailu["MMKilpailu"] == 1)
					echo '<i class="fa fa-circle" title="MM-kilpailut" style="color:blue;"></i> ';
				
				echo "</h3>";
				
				echo "<p>{$kilpailu["Esittelyteksti"]}</p>";

				$kilpailuId = $kilpailu["KilpailuId"];
								
				if ($kilpailu["IlmoittautuminenKaynnissa"] == 1)
				{				
					echo '<div class="btn-group btn-group-justified">';
					echo '<div class="btn-group"><button data-toggle="collapse" type="button" class="btn btn-info btn-sm" data-target="#'.$kilpailuId.'">
						<i class="fa fa-chevron-circle-right"></i> Ilmoittaudu</button></div>';
						
					echo '<div class="btn-group"><button data-toggle="collapse" type="button" class="btn btn-info btn-sm" data-target="#ilmot_'.$kilpailuId.'">
						<i class="fa fa-info-circle"></i> Näytä ilmoittautuneet</button></div>';
					echo '</div>';	
					echo '<div id="'.$kilpailuId.'" class="collapse">';
					 
						$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";				
						echo '<form method="post" action="http://kyykkaliittoapi.azurewebsites.net/ilmoittautuminen">
							<input type="hidden" name="origin" value="'.$actual_link.'" />
							<input type="hidden" name="kilpailuid" value='.$kilpailuId.' />
							<div class="form-group">
								<label for="firstname">Etunimi:</label>
								<input type="text" class="form-control" name="firstname" id="firstname"/>
							</div>
							<div class="form-group">
								<label for="lastname">Sukunimi:</label>
								<input type="text" class="form-control" name="lastname" id="lastname"/>
							</div>
							<div class="form-group">
								<label for="email">Sähköpostiosoite:</label>
								<input type="email" class="form-control" name="email" id="email"/>
							</div>
							<button type="submit" class="btn btn-primary btn-sm">
							<i class="fa fa-sign-in"></i> Lähetä ilmoittautuminen</button>
						</form>';
					
					echo '</div> ';
					
					echo '<div id="ilmot_'.$kilpailuId.'" class="collapse">';
						
					echo '<h4>Ilmoittautuneet pelaajat</h4>';
							if ($kilpailu["Ilmoittautuneet"] != null)
							{
								echo '<table class="table table-condensed"><th>Nimi</th><th>Sarja</th>';
								foreach ($kilpailu["Ilmoittautuneet"] as $ilmo)
								{
									echo '<tr>
										 <td>'.$ilmo["Nimi"].'</td><td>'.$ilmo["Sarja"].'</td>
										 </tr>';
								}
								echo "</table>";
							}
					
					echo '</div> ';
					
				}
				
				echo "</panel>";

			}
		}
				} 
		catch (Exception $e) 
		{
			echo '<div class="alert alert-danger"><strong>Virhe tapahtui</strong><br/>' . $e->getMessage() . '</div>';
		}
	}
}
add_shortcode( 'hae_kilpailukalenteri', 'hae_kilpailukalenteri_func' );


// Register the menu.
add_action( "admin_menu", "kk_plugin_menu_func" );
function kk_plugin_menu_func() {
   add_submenu_page( "options-general.php",  // Which menu parent
                  "Kilpailukalenteri",            // Page title
                  "Kilpailukalenteri",            // Menu title
                  "manage_options",       // Minimum capability (manage_options is an easy way to target administrators)
                  "kilpailukalenteri",            // Menu slug
                  "kk_plugin_options"     // Callback that prints the markup
               );
}

// Print the markup for the page
function kk_plugin_options() {
   if ( !current_user_can( "manage_options" ) )  {
      wp_die( __( "You do not have sufficient permissions to access this page." ) );
   }
   echo "Kilpailukalenterin saa haettua liiton backendistä shortcodella [hae_kilpailukalenteri vuosi=yyyy], jossa yyyy on esimerkiksi 2017";
}

?>