<?= $header ?>
<script>
	window.androidUrl = "<?= $androidUrl ?>";
	window.iosUrl = "<?= $iosUrl ?>";
	window.branchKey = "<?= $branchKey ?>";
</script>
<style>
.background-image-gradient{display:none;}
.appPromo, body.appPromo{
	background-color:#fff !important; /* lower specificity than skin-specific coloring, so we have to make it important */
	font-family: "Helvetica Neue",Arial,sans-serif !important;
}
body.appPromo, body.appPromo::before, body.appPromo::after{
	background-image:none !important;
}
.thumbsWrapper{
	position:relative;
}
.thumbRow{
	margin-left:-<?= round($thumbWidth / 2) ?>px;
	overflow-x:hidden;
	white-space:nowrap;
}<?php
// This is the overlay which makes the image grid slightly faded out ?>
.thumbOverlay {
  z-index: 2;
  display: block;
  position: absolute;
  height: 100%;
  top: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.5);
}
.topContent{
	z-index: 3;
}
.topContent *{
	z-index: 3;
}
.pitchOuter{
	width: 374.2px;
	height: <?= $thumbWidth ?>px;
	background-color: #<?= $config->action_bar_color ?>;/*#275ba3;*/
	position:absolute;
	top: <?= $thumbHeight + $imgSpacing ?>px;
	left: <?= ($thumbWidth * 1.5) + ($imgSpacing * 2); ?>px;
}
.pitchInner{
	width: 299px;
	height: 140.6px;
	margin:24px 0 0 12px;
	text-align:right;
	font-family: "Helvetica Neue","HelveticaNeue-Thin", Arial,sans-serif;
	font-size: 30px;
	line-height: 1.5;
	color: #ffffff;
}
.branchIoOuter{
	position:absolute;
	width:<?= (($thumbWidth + $imgSpacing) * 2) ?>px;
	height:<?= (($thumbHeight + $imgSpacing) * 2) ?>px;
	top: <?= (($thumbHeight + $imgSpacing) * 2) ?>px;
	left: <?= (($thumbWidth * 5.5) + ($imgSpacing * 6)); ?>px;
	background-color:#fff;
	color:#333;
}
.branchIoInner{
	margin: 25px 26px 0 65px;
	line-height:1.4em;
	font-size: 22px;
}
.callToAction{
	margin-top:29px;
	color:#<?= $config->action_bar_color; ?>;
	font-size:18px;
}
.androidPhone{
	position:absolute;
	top: <?= $thumbHeight * 0.55 ?>px;
	left: <?= ($thumbWidth * 3.25) + ($imgSpacing * 3); ?>px;
}
.iosPhone{
	position:absolute;
	top: <?= $thumbHeight * 0.33 ?>px;
	left: <?= ($thumbWidth * 4) + ($imgSpacing * 4); ?>px;
}
.storeButtons{
	margin-top:30px;
}
.storeButtons a:last-child{
	float:right;
}
.storeButtons img{
	width:131px;
	height:44px;
}
.phoneFrame .screenshot{
	position:absolute;
	top:0px;
	left:0px;
}
.androidPhone .screenshot{
	top:52px;
	left:14px;
	width:266px;
	height:473px;
}
.iosPhone .screenshot{
	top:77px;
	left:28px;
	width:269px;
	height:480px;
}
#branchIoForm input[type=text]{
	display:inline-block;
	padding: 0 65px 0 18px;
	line-height:41px;
	font-size:16px;
}
#branchIoForm button{
	display:inline-block;
	border-radius:3px;
	width:75px;
	height:35px;
	margin-left: -80px;
	vertical-align:middle;
	visibility:hidden;
}
.belowThumbs{
	position:relative;
	left: <?= ($thumbWidth * 1.5) + ($imgSpacing * 2); ?>px;
	padding:15px;
}
.backLink{
	z-index:3;
	color:#<?= $config->action_bar_color; ?>;
	font-size:1.5em;
}
</style>
<?= $debug ?>


<div class='thumbsWrapper'>
	<div class='thumbRow'>
	<?php
		$numThumbs = 0;
		foreach($trendingArticles as $topArticle){
			print "<img src='{$topArticle["imgUrl"]}' title='{$topArticle["title"]}' width='{$topArticle["width"]}' height='{$topArticle["height"]}'/>\n";

			$numThumbs++;
			if(($numThumbsPerRow * $thumbRows) == $numThumbs){
				break; // both rows are filled now... stop printing thumbnails
			} else if(($numThumbs % $numThumbsPerRow) == 0){
				print "</div><div class='thumbRow'>\n";
			}
		}
	?>
	</div>
	<div class='thumbOverlay'></div>
	<div class='topContent'>
		<div class='pitchOuter'>
			<div class='pitchInner'>
				<?= wfMsg( 'apppromolanding-pitch' ) ?>
			</div>
		</div>
		<div class='branchIoOuter'>
			<div class='branchIoInner'>
				<p><?= wfMsg('apppromolanding-custompitch', $config->name) ?></p>
				<div class='branchIo'>
					<div class='callToAction'><?= wfMsg( 'apppromolanding-call-to-action' ) ?></div>
					<form id='branchIoForm' method='post' onsubmit='sendSMS()'>
						<input type='text' name='phoneNumber' placeholder='<?= wfMsg('apppromolanding-phone-num-placeholder') ?>'/><button type='submit'>
							<?= wfMsg( 'apppromolanding-button-get' ) ?>
						</button>
					</form>
				</div>
				<div class='storeButtons'>
					<a href='<?= $iosUrl ?>'>
						<img src='<?= $iosStoreSrc ?>'/>
					</a>
					<a href='<?= $androidUrl ?>'>
						<img src='<?= $androidStoreSrc ?>'/>
					</a>
				</div>
			</div>
		</div>
		<div class='phoneWrapper androidPhone'>
			<div class='phoneFrame'>
				<img src='<?= $androidPhoneSrc ?>'/>
				<div class='screenshot'>
					<img src='<?= $androidScreenShot ?>' width='266' height='473'/>
				</div>
			</div>
		</div>
		<div class='phoneWrapper iosPhone'>
			<div class='phoneFrame'>
				<img src='<?= $iosPhoneSrc ?>'/>
				<div class='screenshot'>
					<img src='<?= $iosScreenShot ?>' width='269' height='480'/>
				</div>
			</div>
		</div>
	</div>
</div>
<div class='belowThumbs'>
	<a class='backLink' href='<?= $mainPageUrl ?>'>
		<?= wfMsg( 'apppromolanding-back' ) ?>
	</a>
</div>
<script>
	(function(b,r,a,n,c,h,_,s,d,k){if(!b[n]||!b[n]._q){for(;s<_.length;)c(h,_[s++]);d=r.createElement(a);d.async=1;d.src="https://cdn.branch.io/branch-latest.min.js";k=r.getElementsByTagName(a)[0];k.parentNode.insertBefore(d,k);b[n]=h}})(window,document,"script","branch",function(b,r){b[r]=function(){b._q.push([r,arguments])}},{_q:[],_v:1},"addListener applyCode banner closeBanner creditHistory credits data deepview deepviewCta first getCode init link logout redeem referrals removeListener sendSMS setIdentity track validateCode".split(" "), 0);
	branch.init('<?= $branchKey ?>');

	function sendSMS() {
		branch.sendSMS(
			$('form#branchIoForm input').val(),
			{
				channel: 'Wikia',
				feature: 'Text-Me-The-App',
				campaign: 'apppromolanding'
			}, { make_new_link: false }, // Default: false. If set to true, sendSMS will generate a new link even if one already exists.
			function(err) { console.log(err); }
		);
		return false;
	}
</script>


<!-- TODO: REMOVE EVERYTHING BELOW HERE!! -->
<style>
ul.tempList{
	list-style:disc;
	padding-left:50px;
}
.tempList li{
	color:#000;
	padding:10px;
}
.tempList li.done{
	text-decoration:line-through;
	color:#333;
}
</style>
<div style='clear:both;margin-top:250px;'></div>
Sub-tasks:
<ul class='tempList'>
	<li class='done'>Get the Community_App URL to not be an article, instead to be commandeered by our extension</li>
	<li class='done'>Get the page to display with the Wikia-header only, and our standard other page infrastructure, in a clean way.</li>
	<li class='done'>Pull the configs from the app-config service & memcache them.</li>
	<li class='done'>Get a single wiki's app config from the big config.</li>
	<li class='done'>Build android & ios appstore links from the app's config</li>
	<li class='done'>Have the site automatically redirect android devices to the androidurl and iOS devices to the ios url</li>
	<li class='done'>Get background images from API e.g. http://www.fallout.wikia.com/api/v1/Articles/Top?expand=1&limit=30</li>
	<li class='done'>Set page BG color from $config->action_bar_color</li>
	<li class='done'>Create grid with background images from trending articles API</li>
	<li class='done'>Put a semi-transparent mask obscuring all images (they're BACKGROUND afterall).</li>
	<li class='done'>i18n'ed pitch on the left</li>
	<li class='done'>Find someone in eng (or some docs) and ask why i18n strings can't be used automatically like in normal MediaWiki... I think I vaguely recall there being some cache we have to regenerate to push them through? Answer: ?rebuildmessages=1&lang=en&mcache=writeonly</li>
	<li class='done'>i18n'ed back button, linking to homepage</li>
	<li class='done'>Phone images, positioned & overlapping</li>
	<li class='done'>i18n'ed Call-To-Action with text color: $config->action_bar_color</li>
	<li class='done'>Add store-badge images with links to $androidUrl and $iosUrl</li>
	<li class='done'>Figure out Helvetica Neue situation. It's also used in Mercury. (Figured it out: some browsers have it, others will slide to the fallbacks & that's okay).</li>
	<li class='done'>Branch.io widget:
		<ul>
			<li class='done'>Get the branch_key for the app, from the Branch API</li>
			<li class='done'>We can probably polish/customize the branch.io interaction a bunch. (Ended up mainly being URL params... after submit, there is no interaction)</li>
			<li class='done'>Get it working: https://dev.branch.io/features/text-me-the-app/advanced/</li>
			<li class='done'>Input field... typing a phone number into it causes "GET" button to appear (as if inside the textarea, via padding & negative margins)</li>
		</ul>
	</li>
	<li class='done'>Integrate the screenshots from S3</li>
	<li class='done'>Ensure that this works on a bunch of wikis & looks correct on all of them</li>
	<li>Put the screenshots behind a Fastly bucket.</li>
	<li>Translation config files & translation requests</li>
</ul>
