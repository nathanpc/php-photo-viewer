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
if ($cols == 0)
	$cols = 5;
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
		
		.photo {
			text-align: center;
		}
		
		.photo a {
			text-decoration: none;
		}
		
		.photo img {
			max-width: 100%;
		}
	</style>
</head>
<body>
	<div id="header">
		<h1>Photo Viewer</h1>
		<h2>View your photos from the comfort of your web browser.</h2>
	</div>
	
	<?php try { ?>	
	<?php if ($view === 'folder') { ?>
	<!-- Album view -->
	<?php $gallery = new Gallery($site_root, $photos_root, $path); ?>
	
	<!-- Albums -->
	<div id="albums">
		<?php if (!$gallery->is_viewer_root()) { ?>
			<div class="album">
				<a href="?view=folder&path=<?= urlencode($gallery->parent_path())
						?>&cols=<?= $cols ?>">
					<img src="<?= $site_root ?>/navigate_left.png" />
					<span>Previous</span>
				</a>
			</div>
		<?php } ?>
		
		<?php foreach ($gallery->albums as $album) { ?>
			<div class="album">
				<a href="?view=folder&path=<?= urlencode($album->path)
						?>&cols=<?= $cols ?>">
					<img src="<?= $site_root ?>/folder.png" />
					<span><?= $album->name ?></span>
				</a>
			</div>
		<?php } ?>
	</div>

	<?php if (count($gallery->photos) > 0) { ?>
	<!-- Photos -->
	<table cellspacing="10">
		<tbody>
		<?php for ($i = 0; $i < count($gallery->photos); $i++) { ?>
			<?php $photo = $gallery->photos[$i]; ?>
			
			<?php
				// Should we open or close a table row?
				if ($i == 0) {
					echo "<tr>\n";
				} else if (($i % $cols) == 0) {
					echo "</tr>\n<tr>\n";
				}
			?>

			<td class="photo">
				<a href="?view=photo&path=<?= urlencode($photo->path) ?>">
					<img src="<?= $photo->href() ?>" />
					<p><?= basename($photo->path) ?></p>
				</a>
			</td>
		<?php } ?>
		</tbody>
	</table>
	<?php } ?>
	
	<?php } else if ($view === 'photo') { ?>
	<!-- Single photo view -->
	<?php $photo = new Photo($photos_root, $path); ?>
	
	<table id="photo-detail" cellpadding="0" cellspacing="0" border="0"
			width="100%" height="100%">
		<tbody>
			<!-- Gallery and file links. -->
			<tr>
				<td colspan="2">
					<img src="<?= $site_root ?>/folder.png" />
					<a href="?view=folder&path=<?= urlencode($photo->gallery_path())
						?>&cols=<?= $cols ?>"><?= basename($photo->gallery_path()) ?></a>
					<span> / </span>
					<img src="<?= $site_root ?>/photo_scenery.png" />
					<a href="<?= $photo->href() ?>"><?= basename($photo->path) ?></a>
				</td>
			</tr>
			
			<tr><td height="10"></td></tr>
			
			<tr>
				<td colspan="2">
					<!-- Image preview. -->
					<a href="<?= $photo->href() ?>">
						<img src="<?= $photo->href() ?>" width="100%"
							alt="<?= basename($photo->path) ?>" />
					</a>
				</td>
			</tr>
			
			<tr><td height="10"></td></tr>
			
			<!-- Full details about the file. -->
			<?php $dimen = $photo->dimensions(); ?>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>Size:</b> <?= $photo->file_size() ?> bytes</td></tr>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>Timestamp:</b> <?= $photo->iso8601() ?></td></tr>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2">
				<b>Dimensions:</b> <?= $dimen['width'] ?>x<?= $dimen['height'] ?>
				(<?= $dimen['mp'] ?> Megapixels)
			</td></tr>
			<?php if (isset($photo->exif['Make'])) { ?>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>Make:</b> <?= $photo->exif['Make'] ?></td></tr>
			<?php } ?>
			<?php if (isset($photo->exif['Model'])) { ?>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>Model:</b> <?= $photo->exif['Model'] ?></td></tr>
			<?php } ?>
			<?php if (isset($photo->exif['UndefinedTag:0xA434'])) { ?>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>Lens:</b> <?= $photo->exif['UndefinedTag:0xA434'] ?>
				</td></tr>
			<?php } ?>
			<?php if (isset($photo->exif['ISOSpeedRatings'])) { ?>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>ISO:</b> <?= $photo->exif['ISOSpeedRatings'] ?>
				</td></tr>
			<?php } ?>
			<?php if ($photo->focal_length() > 0) { ?>
			<?php if (isset($photo->exif['COMPUTED']['ApertureFNumber'])) { ?>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>Aperture:</b>
				<?= $photo->exif['COMPUTED']['ApertureFNumber'] ?></td></tr>
			<?php } ?>
			<tr><td height="3"></td></tr>
			<tr><td colspan="2"><b>Focal Length:</b> <?= $photo->focal_length() ?> mm
				</td></tr>
			<?php } ?>
			
			<!-- EXIF Data:
			<?php print_r($photo->exif); ?>
			-->
		</tbody>
	</table>
	
	<?php } else { ?>
		<!-- Invalid view -->
		<?php http_response_code(400); ?>
		<p><b>Error:</b> Invalid view parameter</p>
	<?php } ?>
	
	<?php } catch (Exception $e) { ?>
		<?php http_response_code(500); ?>
		<p><b>Error:</b> <?= $e->getMessage() ?></p>
	<?php } ?>
</body>
</html>
