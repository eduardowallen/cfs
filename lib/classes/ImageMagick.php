<?php

class ImageMagick {
    
    private $convert = '/usr/bin/convert';
    private $mogrify = '/usr/bin/mogrify';
    private $composite = '/usr/bin/composite';
	
    public function constrain($origPath, $thumbPath, $maxWidth, $maxHeight) {
        if (file_exists($origPath)) {
            exec($this->convert." -resize ".$maxWidth."x".$maxHeight."\> ".$origPath." ".$thumbPath);
        	return true;
		} else {
            return false;
        }
    }
    public function IMlogo($origPath, $thumbPath, $maxHeight) {
        if (file_exists($origPath)) {
            exec($this->mogrify." -path ".$thumbPath." -filter Triangle -define filter:support=2 -thumbnail x".$maxHeight." -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define png:compression-filter=5 -define png:compression-level=9 -define png:compression-strategy=1 -define png:exclude-chunk=all -interlace none -colorspace sRGB -strip ".$origPath);
            return true;
        } else {
            return false;
        }             
    }
    public function IMEventLogo($origPath, $thumbPath, $maxHeight) {
        if (file_exists($origPath)) {
            exec($this->mogrify." -path ".$thumbPath." -filter Triangle -define filter:support=2 -thumbnail x".$maxHeight." -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define png:compression-filter=5 -define png:compression-level=9 -define png:compression-strategy=1 -define png:exclude-chunk=all -interlace none -colorspace sRGB -strip ".$origPath);
            return true;
        } else {
            return false;
        }             
    }    
    public function IMmap($origPath, $thumbPath, $maxWidth) {
        if (file_exists($origPath)) {
            exec($this->mogrify." -path ".$thumbPath." -filter Triangle -define filter:support=2 -thumbnail ".$maxWidth." -unsharp 0.25x0.25+8+0.065 -dither None -posterize 136 -quality 82 -define jpeg:fancy-upsampling=off -define png:compression-filter=5 -define png:compression-level=9 -define png:compression-strategy=1 -define png:exclude-chunk=all -interlace none -colorspace sRGB -strip ".$origPath);
            return true;
        } else {
            return false;
        }             
    }

    public function pdf2img($origPath, $thumbPath) {
    	exec($this->convert." -density 96 -quality 96 ".$origPath." ".$thumbPath);
    }
    
    public function crop($origPath, $thumbPath, $x, $y, $width, $height) {
    	exec($this->convert." ".$origPath." -crop ".$width."x".$height."+".$x."+".$y." ".$thumbPath);
    }
    
    public function cropSquareFromMaximum($origPath, $thumbPath, $width) {
        
        $size = getimagesize($origPath);
        
        if ($size[0] > $size[1]) {
            //Landscape
            $this->crop($origPath, $thumbPath, 0, 0, $size[1], $size[1]);
            $from = $thumbPath;
            
        } else if ($size[1] > $size[0]) {
            //Portrait
            $this->crop($origPath, $thumbPath, 0, 0, $size[0], $size[0]);
            $from = $thumbPath;
            
        } else {
            //Square, no cropping needed
            $from = $origPath;
        }
        
        //Constrain the cropped image
        $this->constrain($from, $thumbPath, $width, $width);
        
    }
    
    public function rotate($origPath, $thumbPath, $degrees) {
        exec($this->convert." ".$origPath." -rotate '".$degrees."' ".$thumbPath);
        
    }
	
}
?>
