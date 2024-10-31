<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function rg_stores_main_page()
{
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Welcome to RevGlue Broadband TV & Phone WordPress CMS Plugin</h1>
		<div style="clear:both;"></div>
		<hr/>
		<div class="panel-white mgBot">
			<h3>Introduction</h3>
			<p>RevGlue provides wordPress plugins for affiliates that are free to download and earn 100% commissions. RevGlue provides the following WordPress plugins.</p>
			<ul class="pagelist">
				<li>RevGlue Store  - setup your shopping directory</li>
				<li>RevGlue Coupons – setup your vouchers / coupons website.</li>
				<li>RevGlue Cashback – setup your cashback website in minutes.</li>
				<li>RevGlue Product Feeds – setup your niche product website.</li>
				<li>RevGlue Group Deals – setup your Group Deals aggregation engine in minutes.</li>
				<li>RevGlue Mobile Comparison – setup mobile comparison website in minutes.</li>
				<li>Banners API – add banners on your projects integrated in all plugins above.</li>
				<li>Broadband TV & Phone -  Setup broadband, tv and phone comparison website.</li>
			</ul>
		</div>
		<div class="panel-white mgBot">

			<?php
			$check = rg_check_subscription();
			if ($check=="Free") { ?>
			<h3>RevGlue Broadband Data and WordPress CMS Plugin</h3>
			<p>There are two ways you can obtain Broadband data in this plugin.</p>
			<p> <b> 1 </b> - Subscribe to RevGlue affiliate Broadband data for £60 and add your ownaffiliate network IDs to earn 100% commission on your affiliate network accounts. Try is free for the first 30 days. Create RevGlue.com user account and subscribe with affiliate Broadband data set today. </p>
			<p> <b>2 </b> - You can use RevEmbed Broadband data set that is free to use and you are not required to create affiliate network accounts. RevEmbed data set for Broadband offers 80% commission to you on all the sales referred from your Broadband website. This is based on revenue share basis with RevGlue that saves your time and money and provides you ability to create your Broadband website in minutes. Browse RevEmbed module. Once you register for any both data source from the options given above. You will be provided with the project unique id that you are required to add in Import Broadband section and fetch the broadband data. </p>
			<?php } else{ ?>
				<h3>RevGlue Broadband WordPress CMS Plugin</h3>
				<p>The aim of RevGlue Broadbands plugin is to allow you to setup a UK Broadband TV & Phone comparison website in the UK. You will earn 100% commissions generated via the plugin and the CMS is totally free for all affiliates. You may make further copies or download latest versions from RevGlue website. You will require RevGlue account and then subscribe to RevGlue Broadbands data to setup UK shopping directory. </p>
			<?php } ?>
		</div>
		<div class="panel-white mgBot">
			<h3>RevGlue Broadband Menu Explained</h3>
			<p><b>Dashboard </b>- Summary of Broadband, TV and Phone plugin and useful links.</p>
			<p><b>Import Broadband, Phone & TV </b>- Add your RevGlue Data account credentials to validate your account and obtain RevGlue Broadbands Data. Use CRON file path to setup on your server to auto update the data dynamically.</p>
			<p><b>Stores</b>- Shows all stores data obtained via RevGlue Data API. The Data api only fetches the stores you have selected on your RevGlue account so make sure you have selected all the stores.</p>
			<p><b>Broadband TV & Phone </b>- Browse all your broadband, tv and phones data obtained from RevGlue data api. This data is presented on the website for you.</p>
			<p><b>Import Banners </b>- Add your RevGlue Data account credentials to validate your account and obtain RevGlue Banners Data. Use CRON file path to setup on your server to auto update the data dynamically.</p>
			<p><b>Banners </b>- Allows you to add your own banner on website placements that are pre-defined for you. You may add multiple banners on one placements and they will auto change on each refresh. You may also subscribe with RevGlue Banners API and obtain latest banners for each store from RevGlue Banners. The banners you may add are known as LOCAL banners and others obtained via RevGlue Banner API are shown as RevGlue Banners.</p>
			<p><b>Broadband Reviews </b>- Here you can view all user reviews provided on the broadband website. You can edit, delete or publish them on the website.</p>
			<p><b>Exit Clicks </b>- You can view all your website user clicks from the broadband website to the stores. It shows you who is visiting which store. We recommend adding Google analytics code in the footer of your wordpress plugin php page to view other user statistics such as visits, user behavior and demographics. Learn more about Google analytics on Youtube training videos. Google analytics is free to use.</p>
			<p><b>Newsletter Subscribers  </b>-  Here is the list of all newsletter subscribers for you that have opted in for newsletter on your WordPress broadband cms. You can export the list and send them newsletters with email newsletter software.</p>
		</div>
		<div class="panel-white mgBot">
			<h3>Further Development</h3>
			<p>If you wish to add new modules or require additional design or development changes then contact us support@revglue.com.</a></p>
			<p>We are happy to analyse the required work and provide you a quote and schedule.</p> 
		</div>
		<div class="panel-white mgBot">
			<h3>Useful Links</h3>
			<p><b>RevGlue</b>- <a href="https://www.revglue.com/" target="_blank">https://www.revglue.com/</a></p>
			<p><b>RevGlue Broadband Data</b>- <a target="_blank" href="https://www.revglue.com/data">https://www.revglue.com/data</a></p>
			<p><b>RevGlue WordPress CMS Plugins</b>- <a target="_blank" href="https://www.revglue.com/free-wordpress-plugins">https://www.revglue.com/free-wordpress-plugins</a></p>
			<p><b>RevGlue New Broadband Templates</b>- <a target="_blank" href="https://www.revglue.com/affiliate-website-templates">https://www.revglue.com/affiliate-website-templates</a></p>
		</div>
	</div><?php		
}
