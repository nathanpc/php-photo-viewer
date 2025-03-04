<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
require_once __DIR__ . '/photo_viewer.php';

// Settings
$site_root = dirname($_SERVER['PHP_SELF']);
$photos_root = __DIR__ . '/photos';

// Get URL paramters.
$view = isset($_GET['view']) ? $_GET['view'] : 'folder';
$path = isset($_GET['path']) ? $_GET['path'] : '/';
$cols = isset($_GET['cols']) ? $_GET['cols'] : 5;
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Photo Viewer</title>
	
	<style type="text/css">
		/* Reset everything. */
		* {
			margin: 0;
			padding: 0;
			font-family: Verdana, Arial, sans-serif;
		}
		
		body {
			padding: 10px;
		}
		
		#header {
			margin-bottom: 10px;
		}
		
		#header h2 {
			font-size: 16px;
			font-weight: normal;
		}
		
		#albums {
			margin-top: 15px;
			margin-bottom: 15px;
			text-align: center;
		}
		
		#albums .album {
			display: inline-block;
			height: 24px;
			margin-left: 10px;
			margin-right: 10px;
			font-size: 14px;
			line-height: 24px;
		}
		
		#albums .album a {
			text-decoration: none;
		}
		
		#albums .album img, #albums .album span {
			vertical-align: middle;
		}
	</style>
</head>
<body>
	<div id="header">
		<h1>Photo Viewer</h1>
		<h2>View your photos from the comfort of your web browser.</h2>
	</div>
	
	<?php $gallery = new Gallery($site_root, $photos_root, $path); ?>
	
	<!-- Albums -->
	<div id="albums">
		<?php if (!$gallery->is_viewer_root()) { ?>
			<div class="album">
				<a href="?view=folder&path=<?= urlencode($gallery->parent_path())
						?>&cols=<?= $cols ?>">
					<img src="./navigate_left.png" />
					<span>Previous</span>
				</a>
			</div>
		<?php } ?>
		
		<?php foreach ($gallery->albums as $album) { ?>
			<div class="album">
				<a href="?view=folder&path=<?= urlencode($album->path)
						?>&cols=<?= $cols ?>">
					<img src="./folder.png" />
					<span><?= $album->name ?></span>
				</a>
			</div>
		<?php } ?>
	</div>
	
	<?php if (count($gallery->photos) > 0) { ?>
	<!-- Photos -->
	<table border="1" cellpadding="1" cellpadding="1">
		<tbody>
			<tr>
				<td>Test 1</td>
			</tr>
		</tbody>
	</table>
	<?php } ?>
</body>
</html>
