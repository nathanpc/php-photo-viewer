<?php
/**
 * photo_viewer.php
 * A simple photo gallery/viewer implementation in PHP.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

/**
 * Representation of a folder with sub-folders and photos inside of it.
 */
class Gallery {
	public $photos = array();
	public $albums = array();
	public $name;
	public $site_root;
	public $root;
	public $path;

	/**
	 * Constructs a gallery object.
	 *
	 * @param string $site_root Root of the photo viewer website relative to the
	 *                          server's root. (Allows the gallery to be inside a
								subfolder of the server)
	 * @param string $root      Photo viewer storage root path.
	 * @param string $path      Gallery path relative to the storage root.
	 * @param string $name      Title of the album.
	 */
	public function __construct($site_root, $root, $path, $name = "Album") {
		$this->name = $name;
		$this->site_root = $site_root;
		$this->root = realpath($root);
		$this->path = $path;
		
		// Ensure that the path is inside root and exists.
		if (!$this->path_valid($path))
			throw new Exception("Invalid path");
		
		// Fix double slash in path.
		if ((strlen($path) > 1) && ($path[0] === '/') && ($path[1] === '/'))
			$this->path = substr($path, 1);
		
		// Populate ourselves.
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
				if (is_dir($this->full_path() . "/$entry")) {
					$gallery = new Gallery($this->site_root, $this->root,
						$this->path . "/$entry", $entry);
					array_push($this->albums, $gallery);
				} else {
					array_push($this->photos, $entry);
				}
			}
			
			// Close the directory handle.
			closedir($handle);
		}
	}
	
	/**
	 * Checks if a path relative to the viewer's root is actually inside it and 
	 * exists.
	 *
	 * @param string $path Path relative to the root.
	 *
	 * @return boolean TRUE if the path is inside the photo viewer's root and
	 *                 exists.
	 */
	protected function path_valid($path) {
		$full_path = realpath($this->root . $path);
		
		//If this path is higher than the parent folder
		if(strcasecmp($full_path, $this->root) < 0)
			return false;
		
		return is_dir($full_path);
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
}
