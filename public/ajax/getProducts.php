<?php
	/**
		This file takes the positions ordered products and views them
	***/

	error_reporting(E_ALL);
	ini_set('display_errors','true');

	$parts = explode('/', dirname(dirname(__FILE__)));
	$parts = array_slice($parts, 0, -1);
	define('ROOT', implode('/', $parts).'/');

	session_start();
	require_once ROOT.'config/config.php';
	require_once ROOT.'lib/functions.php';
	require_once ROOT.'lib/classes/Translator.php';

	spl_autoload_register(function ($className) {
		if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
			require_once(ROOT.'lib/classes/'.$className.'.php');

		} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
			require_once(ROOT.'application/models/'.$className.'.php');
		}
	});

	$globalDB = new Database;
	global $globalDB;

	// Samla information från POST-arrayen
	$fair = $_POST['fair'];
	$exhibitor = $_POST['exhibitor'];

	if(!isset($_POST['delete'])) :
		//$level = $_POST['level'];
		$stmt = $globalDB->prepare("SELECT * FROM exhibitor_orders as eo 
			INNER JOIN article as a ON eo.article=a.ArticleId 
			INNER JOIN article_category as ac ON a.ArticleCategory = ac.CategoryId
			WHERE eo.exhibitor = ? AND eo.fair = ?
			ORDER BY ac.CategoryId asc");
		$stmt->execute(array($exhibitor, $fair));
		$result = $stmt->fetchAll();

		$category = 0;
		foreach($result as $article):
			if($category != $article['CategoryId']):
				if($category != 0): echo '</div>'; endif;?>
				<div class="article_category_16 article_categories">
					<div class="art_header">
						<p><?php echo $article['CategoryName']?></p>
						<p class="art_cat_total"><span class="pricetotclean"></span> <span class="value_"><?php echo $_POST['value']?></span></p>
					</div>
			<?php endif; ?>
				<div class="article">
					<?php 
						$price = $article['ArticlePrice'];

						$calculator = new CurrencyCalculator;
						$f = new Fair;
						$f->load($fair, 'id');
						if($_POST['value'] !=$f->get('default_currency')){
							$price = $calculator->convertValue($f->get('default_currency'), $_POST['value'], $price);
						}
					?>
					<span class="artid" style="display:none;"><?php echo $article['ArticleId']?></span>
					<span class="artnr"><?php echo $article['ArticleNum']?></span>
					<span class="artname"><?php echo $article['ArticleName']?></span>
					<span class="artprice"><span class="cleanprice"><?php echo $price?></span> <span class="value_"><?php echo $_POST['value']?></span></span>
					<span class="amount"><?php echo $article['amount']?></span>
					<span class="total"><span class="total_price"><?php echo floatval($price) * floatval($article['amount'])?></span> <span class="value_"><?php echo $_POST['value']?></span></span>
				</div><?php
			$category = $article['CategoryId'];
		endforeach;
	else:
		$stmt = $globalDB->prepare("DELETE FROM exhibitor_orders WHERE exhibitor = ? AND fair = ?");
		$stmt->execute(array($exhibitor, $fair));
	endif;
?>
