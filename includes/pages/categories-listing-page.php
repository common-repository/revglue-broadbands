<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function rg_broadbands_listing_page()
{
    global $wpdb;
	$broadband_table = $wpdb->prefix.'rg_broadband';
	$stores_table = $wpdb->prefix.'rg_stores';
	$broadband_category_table = $wpdb->prefix.'rg_broadband_category';
	$sql = "SELECT * FROM $broadband_table";
	$broadbands = $wpdb->get_results($sql);
	//pre($broadbands);
	//die;
	$sqls = "SELECT rg_store_id, image_url, title FROM $stores_table group by rg_store_id";
		$storeDate = $wpdb->get_results($sqls);
		//pre($storeDate);
		//die;
		$storearray = array();
		foreach($storeDate as $row){
				$storearray[$row->rg_store_id]['image_url'] = $row->image_url;
				$storearray[$row->rg_store_id]['title'] = $row->title;
				$storearray['store_id'] = $row->rg_store_id;
		} 
	?>
	<div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Broadband, TV & Phone</h1>
		<div style="clear:both;"></div>
		<hr/> 
		<div class="text-right">You can filter the results by Broadband name, title, services speed Mbps and cost pm.</div>
		<table id="broadbands_admin_screen" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>RG ID</th>
					<th>Logo</th>
					<th>Broadband</th> 
					<th>Title</th> 
					<th>Category</th>
					<th>Download Limit</th>
					<th>Speed MB</th>
					<th>Cost PM</th> 
					<th>Detail</th>  
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>RG ID</th>
					<th>Logo</th>
					<th>Broadband</th> 
					<th>Title</th> 
					<th>Category</th> 
					<th>Download Limit</th>
					<th>Speed MB</th>
					<th>Cost PM</th>
					<th>Detail</th> 
				</tr>
			</tfoot>
		<tbody>
			<?php
			$ctr = 1;
			foreach ( $broadbands as $single_broadbands )
			{ 
				$storeid = $single_broadbands->rg_store_id; 
				$broadbandid = $single_broadbands->broadband_id; 
				/*pre($single_broadbands);
				die();*/
			?>
				<style type="text/css">
					#strImgZoom<?php echo $ctr; ?>{
						position: absolute;
						right: 0;
						top: 80px;
						left: 185px;
						background: #fff;
						border-radius: 50%;
						width: 15px;
						text-align: center;
						font-size: 25px; 
					}
				</style>
				<tr class="ui-state-default">
					<td> <?php echo $broadbandid; ?> </td>
					<td><img class="strImage thisStoreImg<?php echo $ctr; ?>" src="<?php echo $storearray[$storeid]['image_url'] ; ?>" />
						<a href="javascript:void(0);" 
						   class="getthisstoreid" 
						   data-streid="<?php echo $storeid; ?>" 
						   data-broadbandid="<?php echo $broadbandid; ?>" 
						   id="strImgZoom<?php echo $ctr; ?>" 
						   data-counter="<?php echo $ctr; ?>"
						   data-toggle="modal" 
						   data-target="#yourModal<?php echo $ctr; ?>"						   
						   style="position: absolute;">
							<!-- <i class="fa fa-search" aria-hidden="true"></i> -->
						</a> 
					</td>
					<td style="text-align:left;"><?php echo $storearray[$storeid]['title'] ; ?></td> 
					<td style="text-align:left;">
						<a title="<?php echo $single_broadbands->deeplink; ?>" target="_blank" href="<?php echo $single_broadbands->deeplink; ?>">
						<?php echo $single_broadbands->broadband_title; ?>
							
						</a>
							
						</td>
					<td style="text-align:left;"><?php $ctID = $single_broadbands->broadband_category_id; 
					$sqlc = "SELECT title FROM `$broadband_category_table` WHERE broadband_category_id = $ctID ";
					$CateName = $wpdb->get_var($sqlc);
					echo $CateName;
					// Developed by Jawad Saeed 
					?></td>
					<td style="text-align:left;"><?php echo  $single_broadbands->download_limit =="Unlimited" ? "Unlimited" : $single_broadbands->download_limit ." GB" ; ?></td>
					<td style="text-align:left;"><?php echo $single_broadbands->speed." MB"; ?></td>
					<td style="text-align:left;"><?php echo "£".$single_broadbands->cost_per_month_for_filter; ?></td>
					<td style="text-align:left;"> 
						<!-- Trigger the modal with a button -->
						<a  
						href="javascript:void(0);"
						class="btn btn-primary btn-primary bbdetail"   
						data-storeid="<?php echo $storeid; ?>" 
						data-broadbandid="<?php echo $broadbandid; ?>" 
						data-counter="<?php echo $ctr; ?>" 
						data-toggle="modal" 
						data-target="#myModal<?php echo $ctr; ?>">Detail</a>   
					</td> 
				</tr>
				<style type="text/css" id="page-css">
					  .scroll-pane{ 
						width: 100%; 
						height: 200px;
						overflow: auto;
					  }
					  .horizontal-only{ 
						height: auto; 
						max-height: 200px;
					  }
					  .scroll-pane<?php echo $ctr; ?>{ 
						max-height: 600px !important;
					  }  
				</style>
			<!-- Modal -->
			<div class="modal fade" id="myModal<?php echo $ctr; ?>" role="dialog">
				<div class="modal-dialog">
				  <!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
					  <button type="button" class="close" data-dismiss="modal">&times;</button>
					  <h4 class="modal-title text-center" id="broadband_titlebbpopup<?php echo $ctr; ?>"></h4>
					</div>
					<div class="center">
					<div id="loadergif<?php echo $ctr; ?>" style="display: none;"><img src=<?php echo RGSTORE__PLUGIN_URL."/admin/images/loading.gif" ?> /></div>
				</div>
					<div class="modal-body">
					  <div class="imagecontainer text-center">
						<div class="StoreImgContainer">
						 <img class="storeimge" id="storeimg<?php echo $ctr; ?>" src="" />
						 <p class="Storetitlebb" ></p>
						</div>
					 </div>
					 <div class="clear"></div>
					<hr>
<style>
.jspPane{ width:100% !important; }
</style>
					<div class="popupItemContainer">  
					  <div class="scrollheight scroll-pane<?php echo $ctr; ?>" style=" max-height: 400px;">
						<p><span class="itemTitle">Service: &nbsp;</span><span id="servicesbbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Download Limit: &nbsp;</span><span id="download_limitbbpop<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Deal Type: &nbsp;</span><span id="deal_typebbpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Speed: &nbsp;</span><span id="speedbbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Setup Cost: &nbsp;</span><span id="setup_costbbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Cost per Month: &nbsp;</span><span id="cost_per_monthbbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">No of Contract Months: &nbsp;</span><span id="no_of_contract_monthbbpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Total Contract Cost: &nbsp;</span><span id="total_contract_costpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">First Month Cost: &nbsp;</span><span id="first_month_costpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Cost after First Month: &nbsp;</span><span id="cost_after_first_monthpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Cost after X Month: &nbsp;</span><span id="cost_after_x_monthpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Standard Cost: &nbsp;</span><span id="standard_costpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Upfront: &nbsp;</span><span id="upfront_costpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Router: &nbsp;</span><span id="routerbbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Router Detail: &nbsp;</span><span id="router_detail_bbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Router Price: &nbsp;</span><span id="router_pricepopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Online Discount: &nbsp;</span><span id="online_discountpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Phone Line: &nbsp;</span><span id="phone_linepopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Line Rent: &nbsp;</span><span id="line_rentalpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Promotion: &nbsp;</span><span id="promotionpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Promotion Detail: &nbsp;</span><span id="promotion_detailpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Promotion Issue Date: &nbsp;</span><span id="promotion_issue_datepopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Promotion Expiry Date: &nbsp;</span><span id="promotion_expiry_datepopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Cashback: &nbsp;</span><span id="cashbackpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Delivery Charges: &nbsp;</span><span id="delivery_chargespopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Tech Support: &nbsp;</span><span id="tech_supportpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Tech Telephone: &nbsp;</span><span id="tech_telephonepopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Broadband Type: &nbsp;</span><span id="broadband_typepopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">TV: &nbsp;</span><span id="tvbbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Movies: &nbsp;</span><span id="moviesbbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Sports Channel: &nbsp;</span><span id="sports_channelbbpopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">TV Quality: &nbsp;</span><span id="tv_qualitypopup<?php echo $ctr; ?>"></span></p> 
						<p><span class="itemTitle">Issue Date: &nbsp;</span><span id="issue_datebbpopup<?php echo $ctr; ?>"></span></p>
						<p><span class="itemTitle">Expiry Date: &nbsp;</span><span id="expiry_datebbpopup<?php echo $ctr; ?>"></span></p>  
					</div>  
					</div>  
					<div class="clear"></div>
					</div>
					<div class="modal-footer center"> 
					   <div class="displayBlockInLinline">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button> 
					 </div>
					</div>
				  </div>
				</div>
			</div>
				<!-- The Modal for Store Image -->
				<div id="yourModal<?php echo $ctr;?>" class="modal">
					<!-- Modal content -->
					<div class="modal-contents">
						<span  class="close" data-dismiss="modal">&times;</span>
						<div class="text-center">
							<img id="strImage<?php echo $ctr;?>" src="" />
						</div>
					</div>
				</div>
		<?php 
		$ctr++;
	  } 
		?>
			</tbody>
		</table>
	<?php 
}
