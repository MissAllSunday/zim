<?php

namespace Models;

class Images
{
	protected $_imageUrl = '',
		$_thumbUrl = '',
		$_imagePath = '',
		$_thumbPath = '',
		$_pf = 'tn_';

	function __construct()
	{
		$this->f3 = \Base::instance();

		$this->_imagePath = $this->f3->get('ROOT') . '/images';
		$this->_thumbPath = $this->_imagePath .'/thumbnails';
		$this->_imageUrl = $this->f3->get('URL') . '/images';
		$this->_thumbUrl = $this->_imageUrl . '/thumbnails';
	}

	function extractImage($body = '')
	{
		// Fill up some generic data in case the one we need doesn't exists.
		$image = [
			'thumbUrl' => $this->_thumbUrl .'/tn_default.jpg',
			'imageUrl' => $this->_imageUrl .'/default.jpg',
			'width' => 400,
			'height' => 175,
		];

		$doc = new \DOMDocument;

		// Shhhh!
		libxml_use_internal_errors(true);

		$doc->loadHTML($body);

		// Lets get the first image, it is usually the only one anyways.
		$metaTags = $doc->getElementsByTagName('meta');

		// This largely depends on me doing my fair share of work...
		if (!empty($metaTags))
			foreach ($metaTags as $meta)
				switch ($meta->getAttribute('itemprop'))
				{
					case 'url':
					$file = basename(parse_url($meta->getAttribute('content'))['path']);

					if (file_exists($this->_imagePath .'/'. $file))
					{
						$image['imageUrl'] = $this->_imageUrl .'/'. $file;

						// Thumbnail anyone?
						if (file_exists($this->_thumbPath .'/'. $this->_pf . $file))
							$image['thumbUrl'] = $this->_thumbUrl .'/'. $this->_pf . $file;

						// No? how about creating it then
						else
							$this->createThumb($file);

						// Still doesn't exists? use the full one.
						if (!file_exists($this->_thumbPath .'/'. $this->_pf . $file))
							$image['thumbUrl'] = $image['imageUrl'];
					}

					// No? fine then!
					else
						$image['imageUrl'] = $image['thumbUrl'] = $meta->getAttribute('content');

					break;

					case 'width':
					$image['width'] = $meta->getAttribute('content');
					break;

					case 'height':
					$image['height'] = $meta->getAttribute('content');
					break;
				}

		// No? try an img tag.
		else
		{
			$imgTag = $doc->getElementsByTagName('img');

			if (!empty($imgTag))
			{
				$file = basename(parse_url($meta->getAttribute('src'))['path']);

				if (file_exists($this->_imagePath .'/'. $file))
				{
					$image['imageUrl'] = $this->_imageUrl .'/'. $file;

					// Thumbnail anyone?
					if (file_exists($this->_thumbPath .'/'. $this->_pf . $file))
						$image['thumbUrl'] = $this->_thumbPath .'/'. $this->_pf . $file;

					// No? how about creating it then
					else
						$this->createThumb($file);

					// Still doesn't exists? use the full one.
					if (!file_exists($this->_thumbPath .'/'. $this->_pf . $file))
						$image['thumbUrl'] = $image['imageUrl'];
				}
			}
		}

		// bye bye!
		return $image;
	}

	function createThumb($imgStr = '', $width = 150)
	{
		$imgSource = $this->_imagePath .'/'. $imgStr;

		// Theres nothing to work with.
		if (empty($imgSource) || !file_exists($imgSource))
			return false;

		$fileName = pathinfo($imgSource, PATHINFO_FILENAME);
		$ext = pathinfo($imgSource, PATHINFO_EXTENSION);

		// Why bother?
		if (file_exists($this->_thumbPath . '/'. $this->_pf . $fileName .'.'. $ext))
			return true;

		// Cool beans
		$imgO = new \Image($imgSource, true, '');

		// Create the thumb.
		$imgO->resize($width, null, false);
		$thumbName = $this->_thumbPath .'/'. $this->_pf . $fileName .'.'. $ext;

		// Write it.
		$this->f3->write($fileName . $ext, $imgO->dump(($ext == 'jpg' ? 'jpeg' : $ext), ($ext == 'jpg' ? 75 : 6)));

		return file_exists($this->_thumbPath . '/'. $this->_pf . $fileName .'.'. $ext);
	}
}
