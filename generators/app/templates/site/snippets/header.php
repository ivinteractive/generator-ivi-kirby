<html>

	<head>

		<?= r($page->noindex()->isTrue(), '<meta name="robots" content="noindex, nofollow">') ?>

	  	<?= $page->metaTags() ?>
		<?= $page->socialTags() ?>
		<?= $page->canonicals() ?>

		<?= css([

		]) ?>

		<?= $page->extraCSS() ?>
		<?= css('@auto') ?>

		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?= c::get('gtm-id') ?>');</script>
		<!-- End Google Tag Manager -->

	</head>

	<body id="<?= $page->slug() ?>" class="<?= $page->template() ?>">

		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= c::get('gtm-id') ?>"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->

		<header>


		</header>