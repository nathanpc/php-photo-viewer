<?php
/**
 * photo_viewer.php
 * A simple photo gallery/viewer implementation in PHP.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

/**
 * Abstraction of a photo in a gallery.
 */
class Photo {
	public $abs_path;
	public $path;
	public $exif;
	private $server_path;
	private $thumb_root;

	/**
	 * Construcs a photo object from a file path.
	 *
	 * @param string  $root       Photo viewer storage root path.
	 * @param string  $path       Path to the photo file relative to the root
	 *                            gallery.
	 * @param string  $thumb_root Absolute path to the root of the thumbnails
	 *                            directory.
	 * @param boolean $populate   Should we populate the object with its child
	 *                            elements?
	 */
	public function __construct($root, $path, $thumb_root, $populate = true) {
		$this->thumb_root = $thumb_root;
		$this->path = $path;
		$this->abs_path = realpath($root . $path);
		$this->server_path = Gallery::PathRelRoot($this->abs_path);

		// Check if the path is actually valid.
		if (!Gallery::IsPathValid($root, $this->path))
			throw new Exception("Invalid path");

		// Get EXIF data from image.
		if ($populate)
			$this->exif = @exif_read_data($this->abs_path);
	}

	/**
	 * Gets the path to the gallery that contains this photo.
	 *
	 * @return string Path to the gallery that contains this photo.
	 */
	public function gallery_path() {
		return str_replace('\\', '/', dirname($this->path));
	}

	/**
	 * Gets the path to the image relative to the server root.
	 *
	 * @return string Path to the image relative to the server root.
	 */
	public function href() {
		return $this->server_path;
	}

	/**
	 * Gets the file size in bytes.
	 *
	 * @return File size in bytes.
	 */
	public function file_size() {
		return $this->exif['FileSize'];
	}

	/**
	 * Gets the timestamp of the photo in ISO8601 format.
	 *
	 * @return string Timestamp when this photo was taken in ISO8601 format.
	 */
	public function iso8601() {
		return date('Y-m-d H:i:s', $this->exif['FileDateTime']);
	}

	/**
	 * Gets the photo dimensions.
	 *
	 * @return array Associative array with width, height, and megapixels.
	 */
	public function dimensions() {
		$computed = $this->exif['COMPUTED'];
		$mp = ($computed['Width'] * $computed['Height']) / 1000000;

		return array(
			'width' => $computed['Width'],
			'height' => $computed['Height'],
			'mp' => number_format($mp, 1)
		);
	}

	/**
	 * Computes the focal length used when taking this photo.
	 *
	 * @return int Focal length or 0 if we couldn't compute one.
	 */
	public function focal_length() {
		// Do we even have something to compute?
		if (!isset($this->exif['FocalLength']))
			return 0;

		// Compute the focal length.
		$parts = explode('/', $this->exif['FocalLength']);
		$flen = floatval($parts[0]) / floatval($parts[1]);

		return (int)round($flen);
	}

	/**
	 * Gets the href link for a thumbnail of this image.
	 *
	 * @warning This method will generate a thumbnail if needed.
	 *
	 * @return string Link to the thumbnail of the image.
	 */
	public function thumb_href() {
		$thumb_path = $this->thumb_root . $this->path;

		// Return the file if it already exists.
		if (file_exists($thumb_path))
			return Gallery::PathRelRoot($thumb_path);

		// Ensure that the directory exists.
		$dir = dirname($thumb_path);
		if (!is_dir($dir))
			mkdir($dir, 0755, true);

		// Generate the thumbnail.
		$img = self::GenerateThumbnail($this->abs_path);
		$img->writeImage($thumb_path);

		return Gallery::PathRelRoot($thumb_path);
	}

	/**
	 * Generates a thumbnail for an image.
	 *
	 * @param string $path     Path to the image to have its thumbnail generated.
	 * @param int    $max_size Maximum size of each side of the image.
	 *
	 * @return Imagick Generated thumbnail image.
	 */
	public static function GenerateThumbnail($path, $max_size = 400) {
		// Perform the resize operation.
		$img = new Imagick($path);
		$img->thumbnailImage($max_size, $max_size, true);

		return $img;
	}
}

/**
 * Representation of a folder with sub-folders and photos inside of it.
 */
class Gallery {
	public $photos = array();
	public $albums = array();
	public $name;
	public $site_root;
	public $thumb_root;
	public $root;
	public $path;

	/**
	 * Constructs a gallery object.
	 *
	 * @param string  $site_root  Root of the photo viewer website relative to the
	 *                            server's root. (Allows the gallery to be inside
								  a subfolder of the server)
	 * @param string  $thumb_root Absolute path to the root of the thumbnails
	 *                            directory.
	 * @param string  $root       Photo viewer storage root path.
	 * @param string  $path       Gallery path relative to the storage root.
	 * @param string  $name       Title of the album.
	 * @param boolean $populate   Should we populate the object with its child
	 *                            elements?
	 */
	public function __construct($site_root, $thumb_root, $root, $path,
			$name = "Album", $populate = true) {
		$this->name = $name;
		$this->site_root = $site_root;
		$this->thumb_root = $thumb_root;
		$this->root = realpath($root);
		$this->path = $path;

		// Ensure that the path is inside root and exists.
		if (!self::IsPathValid($this->root, $path))
			throw new Exception("Invalid path");

		// Fix double slash in path.
		if ((strlen($path) > 1) && ($path[0] === '/') && ($path[1] === '/'))
			$this->path = substr($path, 1);

		// Populate ourselves.
		if ($populate)
			$this->populate();
	}

	/**
	 * Reads the contents of the specified path and populates our internal
	 * variables.
	 */
	protected function populate() {
		// Open a directory handle.
		if ($handle = opendir($this->full_path())) {
			// Go through the directory's contents.
			while (false !== ($entry = readdir($handle))) {
				// Ignore special and dot files.
				if ($entry[0] == '.')
					continue;

				// Store each entry in its rightful place.
				$path = $this->path . "/$entry";
				if (is_dir($this->full_path() . "/$entry")) {
					$gallery = new Gallery($this->site_root, $this->thumb_root,
						$this->root, $path, $entry, false);
					array_push($this->albums, $gallery);
				} else {
					array_push($this->photos, new Photo($this->root, $path,
						$this->thumb_root));
				}
			}

			// Close the directory handle.
			closedir($handle);
		}
	}

	/**
	 * Gets the full path to the gallery folder.
	 *
	 * @return string Absolute path to the gallery folder.
	 */
	public function full_path() {
		return realpath($this->root . $this->path);
	}

	/**
	 * Checks if the gallery is the viewer's root gallery.
	 *
	 * @return boolean TRUE if the gallery is in fact the root one and contains
	 *                 no parent.
	 */
	public function is_viewer_root() {
		return $this->root === $this->full_path();
	}

	/**
	 * Gets the path to the parent folder.
	 *
	 * @return Path of the parent folder or '/' if already at root.
	 */
	public function parent_path() {
		if ($this->is_viewer_root())
			return '/';

		return str_replace('\\', '/', dirname($this->path));
	}

	/**
	 * Checks if a path relative to the root is actually inside it and exists.
	 *
	 * @param string $root Parent directory.
	 * @param string $path Path relative to the root.
	 *
	 * @return boolean TRUE if the path is inside the root and exists.
	 */
	public static function IsPathValid($root, $path) {
		$full_path = realpath($root . $path);

		// Check if the path is higher than the parent folder.
		if(strcasecmp($full_path, $root) < 0)
			return false;

		return file_exists($full_path);
	}

	/**
	 * Gets the path to the gallery relative to the server root.
	 *
	 * @param string $path File system path that's inside the server root.
	 *
	 * @return string Path relative to server's root.
	 */
	public static function PathRelRoot($path) {
		return str_replace('\\', '/', substr($path, strlen($_SERVER['DOCUMENT_ROOT'])));
	}
}
