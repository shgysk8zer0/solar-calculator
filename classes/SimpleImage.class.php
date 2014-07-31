<?php

/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/
	
	class SimpleImage {
	
		public $image;
		public $image_type;
		public $fname;
		
		public function __construct($filename) {
			$this->fname = $filename;
			$image_info = getimagesize($filename);
			$this->image_type = $image_info[2];
			
			if( $this->image_type == IMAGETYPE_JPEG ) {
				$this->image = imagecreatefromjpeg($filename);
			}
			elseif( $this->image_type == IMAGETYPE_GIF ) {
				$this->image = imagecreatefromgif($filename);
			}
			elseif( $this->image_type == IMAGETYPE_PNG ) {
				$this->image = imagecreatefrompng($filename);	
			}
		}
		
		public function img_data_uri($type = 'jpeg'){
			ob_start();
			switch($type){
				case 'png': imagepng($this->image); break;
				case 'gif': imagegif($this->image); break;
				default: imagejpeg($this->image); $type='jpeg';
			}
			imagepng($this->image);
			$contents = ob_get_contents();
			ob_end_clean();
			return "data:image/$type;base64," . base64_encode($contents);
		}
		
		public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 90, $permissions=null) {
			if( $image_type == IMAGETYPE_JPEG ) {
				imagejpeg($this->image,$filename,$compression);
			}
			elseif( $image_type == IMAGETYPE_GIF ) {
				imagegif($this->image,$filename);
			}
			elseif( $image_type == IMAGETYPE_PNG ) {
				imagepng($this->image,$filename);
			}
			if( $permissions != null) {
				chmod($filename,$permissions);
			}
		}
		
		public function output($image_type=IMAGETYPE_JPEG) {
			if( $image_type == IMAGETYPE_JPEG ) {
				imagejpeg($this->image);
			}
			elseif( $image_type == IMAGETYPE_GIF ) {
				imagegif($this->image);
				}
			elseif( $image_type == IMAGETYPE_PNG ) {
				imagepng($this->image);
			}
		}
		
		public function getWidth() {
			return imagesx($this->image);	
		}
		
		public function getHeight() {
			return imagesy($this->image);
		}
		
		public function min_dim($min = 0, $overwrite = false){
			$width = $this->getWidth();
			$height = $this->getHeight();
			if(($width < $min) && ($height < $min)){
				($width >= $height) ? $this->resizeToWidth($min) : $this->resizeToHeight($min);
				if($overwrite){
					$this->save($this->fname);
				}
			}
		}
		
		public function max_dim($max = 0, $overwrite = false){
			$width = $this->getWidth();
			$height = $this->getHeight();
			if(($width > $max) || ($height > $max)){
				($width >= $height) ? $this->resizeToWidth($max) : $this->resizeToHeight($max);
				if($overwrite){
					$this->save($this->fname);
				}
			}
		}
		
		public function resizeToHeight($height) {
			$ratio = $height / $this->getHeight();
			$width = $this->getWidth() * $ratio;
			$this->resize($width,$height);
		}
		
		public function resizeToWidth($width) {
			$ratio = $width / $this->getWidth();
			$height = $this->getheight() * $ratio;
			$this->resize($width,$height);
		}
		
		public function scale($scale) {
			$width = $this->getWidth() * $scale/100;
			$height = $this->getheight() * $scale/100;
			$this->resize($width,$height);
		}
		
		public function resize($width,$height) {
			$new_image = imagecreatetruecolor($width, $height);
			imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, 	$width, $height, $this->getWidth(), $this->getHeight());
			$this->image = $new_image;
		}
	}
?>
