<?php

/**
 * Generates svg from raster
 *
 * @param GDImageIdentifier $img    Raster image to convert to svg
 * @return string                   SVG xml
 */
function generateSVG($img) {
    $w = imagesx($img); // image width
    $h = imagesy($img); // image height
    $n = 1; //number of consecutive pixels

    $svgv = "<svg xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"crispEdges\">";
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y = $y+$n) {
            $col = imagecolorat($img, $x, $y);
            $n = 1;

            while(
                ($y+$n < $h) &&
                ($col == imagecolorat($img, $x, ($y+$n)))
            ) {
                $n++;
            }

            $rgb = imagecolorsforindex($img, $col);
            $color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

            if ($rgb["alpha"] && ($rgb["alpha"] < 128 )) {
                $alpha = (128 - $rgb["alpha"]) / 128;
                $color .= "\" fill-opacity=\"$alpha";
            }

            $svgv .= "<rect x=\"$x\" y=\"$y\" width=\"1\" height=\"$n\" fill=\"$color\"/>\n";
        }
    }
    $svgv .= '</svg>';

    $n = 1; //reset number of consecutive pixels
    $svgh = "<svg xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"crispEdges\">";
    for ($y = 0; $y < $h; $y++) {
	    for ($x = 0; $x < $w; $x = $x+$n) {
            $col = imagecolorat($img, $x, $y);
            $n = 1;

            while(
                ($x+$n < $w) &&
                ($col == imagecolorat($img, ($x+$n), $y))
            ) {
                $n++;
            }

            $rgb = imagecolorsforindex($img, $col);
            $color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

            if ($rgb["alpha"] && ($rgb["alpha"] < 128 )) {
                $alpha = (128 - $rgb["alpha"]) / 128;
                $color .= "\" fill-opacity=\"$alpha";
            }

            $svgh .= "<rect x=\"$x\" y=\"$y\" width=\"$n\" height=\"1\" fill=\"$color\"/>\n";
        }
    }
    $svgh .= '</svg>';
    if (strlen($svgh) < strlen($svgv)) $svg = $svgh; else $svg = $svgv;
    return $svg;
}

/**
 * Get an image from a URL or file path
 *
 * @param string $url   Url or path to local file
 * @return GDImageIdentifier
 */
function loadImage( $url ) {
    // Handle URLS
    if( filter_var($url, FILTER_VALIDATE_URL) ){
        $ch = curl_init();
        $timeout = 0;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        // Getting binary data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        $image = curl_exec($ch);
        curl_close($ch);
        // output to browser

        $img = @imagecreatefromstring($image);
    }
    // Handle local files
    else if ( file_exists( $url ) ){
        $img = @imagecreatefromstring( file_get_contents( $url ) );
    }
    else {
        throw new \LogicException( 'Url is invalid.' );
    }

    return $img;
}

?>
