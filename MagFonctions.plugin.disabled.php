<?php
/*
@name MagFonctions
@author fran6t <trautmann@wse.fr>
@link http://blog.passion-tarn-et-garonne.info
@licence Ma Licence
@version 1.0.0
@description Le plugin est necessaire au bon fonctionnement du thème Mag
*/


function MagFonctions_plugin_menu(&$myUser){
	/* Ajoutez un code qui s'executera après le menu des flux ex :
	
	echo '<aside class="squeletteMenu">
				
				<h3 class="left">'._t('P_SQUELETTE_ALIRE').'</h3>
					<ul class="clear">  							  								  							  							  								  	
					<li>
						<ul> 
							<li> 
								<img src="plugins/squelette/img/read_icon.png">
								
									<a href="action.php?action=squelette_action">'.Functions::truncate("Hourra pour Leed et vive les navets!!",30).'</a>
										  
								<button class="right" onclick="squelette_javascript()" style="margin-left:10px;">
									<span>Pouet</span>
								</button>
								</li>
						</ul>
					</li>
				</ul>
			</aside>';
		*/	
}

function MagFonctions_plugin_action($_,$myUser){

	/* Ajoutez un code qui s'executera en tant qu'action ex : 
	
	if($_['action']=='squelette_action'){
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if($_['state']=='add'){
			$return = mysql_query('INSERT INTO '.MYSQL_PREFIX.'plugin_feaditlater (event)VALUES(\''.$_['id'].'\')');
		}else{
			$return = mysql_query('DELETE FROM '.MYSQL_PREFIX.'plugin_feaditlater WHERE event=\''.$_['id'].'\'');
		}
		if(!$return) echo mysql_error();
	}
	*/
}

/* Ajout du css du squelette en en tête de leed
      - par défaut, s'il existe, le fichier default.css est ajouté
      - par défaut, s'il existe, le fichier "nomDuTheme".css est ajouté
      - si vous souhaitez inclure un fichier css supplémentaire (pour tous les thèmes) */
//Plugin::addCss("/css/style.css"); 

//Ajout du javascript du squelette au bas de page de leed
//Plugin::addJs("/js/main.js"); 
 
//Ajout de la fonction squelette_plugin_displayEvents au Hook situé après le menu des flux
//Plugin::addHook("menu_post_folder_menu", "Mag-plugin_plugin_menu");  
//Ajout de la fonction squelette_plugin_action à la page action de leed qui contient tous les traitements qui n'ont pas besoin d'affichage (ex :supprimer un flux, faire un appel ajax etc...)
//Plugin::addHook("action_post_case", "Mag-plugin_plugin_action"); 
class FunctionsMag
{
    private $id;
    public $debug=0;
    	/* Retourner la premiere image superieure à une certaine taille d'un billet 
	 * Dans la version 2 il faudra ajouter un systeme de cache et aussi savoir parser aussi les .png sans les tlchs 
	 * Dans la version 1 on s'occupe uniquement des jpg 
	 * 
	 * @param string $text String dans laquelle chercher les images.
	 * @param integer $taille Taille minimale de l'image (pour exclure comme cela tout ce qui picto FB tweet...
	 * */
	public static function extractIMG($text, $taille = 200){
		$valretour = "";
		$doc = new DOMDocument();
		$doc->loadHTML($text);
		$list = $doc->getElementsByTagName('img');
		foreach ($list as $node) {
			if ($node->hasAttribute('src')) {
				$img_loc = $node->getAttribute('src');
				$filename = basename($img_loc);
				$extension = explode(".",$filename);
				// Si c'est un jpg on traite sinon suivant
				if (strtoupper(end($extension)) == "JPG"){ 
					$blah = getjpegsize($img_loc);
					// Si superieur au mini requis on sort c'est ok
					if ($blah[0] > $taille || $blah[1] > $taille){
						//echo "<br />src " . $src;
						//echo "<br />liste des images " . $filename;
						//$blah = getimagesize($src);
						//echo "<br />type= " . $type = $blah['mime'];
						//echo "<br />X= " . $blah[0]."x".$blah[1];
						//$node->setAttribute('src', 'http://static.images.monsite.com/images/' . $filename);
						$valretour = "<img src=\"".$img_loc."\"  class=\"align-left\" />";
						break;
					}
				}
			}
		}
		return $valretour;
	}

	/* Couper le texte d'un billet comme il faut en ignorant les balises HTML
	 * qui peuvent etre genante a img br (paragraphes vides) 
	 * puis renvoyer ce texte expurgé des balises img 
	 * 
	 * @param string $text String dans laquelle opérer.
	 * 
	 * */
	public static function txtCHAP($text){
		$text = truncateHtml($text, $length = 200, $ending = '...', $exact = false, $considerHtml = true);
		$doc = new DOMDocument();
		$doc->loadHTML($text);
		
		// ce qui suit fonctionne pas pour retirer tous les liens
		// surement un probleme d'index dans l'itérations
		/*
		$nodes=$doc->getElementsByTagName('img');
		foreach($nodes as $node){
			// suprimer le noeud (formule tordu, mais dom)
			$node->parentNode->removeChild($node);
		}
		*/
		
		// Voici ce qui fonctionne
		// On commence par les img
		$domNodeList = $doc->getElementsByTagname('img');
		$domElemsToRemove = array();
		foreach ( $domNodeList as $domElement ) {
			// ...do stuff with $domElement...
			$domElemsToRemove[] = $domElement;
		}
		
		// les url
		$domNodeList = $doc->getElementsByTagname('a');
		foreach ( $domNodeList as $domElement ) {
			// ...do stuff with $domElement...
			$domElemsToRemove[] = $domElement;
		}
		
		// les iframes (cas ou un billet contient uniquement une video youtube par exemple
		$domNodeList = $doc->getElementsByTagname('iframe');
		foreach ( $domNodeList as $domElement ) {
			// ...do stuff with $domElement...
			$domElemsToRemove[] = $domElement;
		}
		
		// les br
		$domNodeList = $doc->getElementsByTagname('br');
		foreach ( $domNodeList as $domElement ) {
			// ...do stuff with $domElement...
			$domElemsToRemove[] = $domElement;
		}
		
		// Suprimer les paragraphes vides
		$domNodeList = $doc->getElementsByTagname('p');
		foreach ( $domNodeList as $domElement ) {
			// si y a rien entre le paragraphe on vire
			if (strlen(trim($domElement->nodeValue)) == 0){
				$domElemsToRemove[] = $domElement;
			}
		}
		
		// Maintenant on fait réellment le job
		foreach( $domElemsToRemove as $domElement ){
			$domElement->parentNode->removeChild($domElement);
		} 
		$body = $doc->getElementsByTagName('body')->item(0);
		// On retourne que ce qu'il y a entre body car sinon c'est structure avec la descri du dom
		return innerHTML($body);
		//return htmlspecialchars(innerHTML($body));
		//return $doc->saveHTML();
	}

} 

	/**
     * Permet à l'issue de l'utilisation de dom de retourner la chaine de caractere sinon 
     * c'est une vraie structure complète qui est retournée
     * 
     * */
	function innerHTML($el) {
		$doc = new DOMDocument();
		$doc->appendChild($doc->importNode($el, TRUE));
		$html = trim($doc->saveHTML());
		$tag = $el->nodeName;
		return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
	}


    /**
     * Fonction trouvée sur http://alanwhipple.com/2011/05/25/php-truncate-string-preserving-html-tags-words/
	* truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
	*
	* @param string $text String to truncate.
	* @param integer $length Length of returned string, including ellipsis.
	* @param string $ending Ending to be appended to the trimmed string.
	* @param boolean $exact If false, $text will not be cut mid-word
	* @param boolean $considerHtml If true, HTML tags would be handled correctly
	*
	* @return string Trimmed string.
	*/
	function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
					// if tag is a closing tag
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
						unset($open_tags[$pos]);
						}
					// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length) {
					break;
				}
			}
		} else {
			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if($considerHtml) {
			// close all unclosed html-tags
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}

		
		
	/**
     * Retrieve JPEG width and height without downloading/reading entire image.
     */
	function getjpegsize($img_loc) {
		$handle = fopen($img_loc, "rb") or die("Invalid file stream.");
		$new_block = NULL;
		if(!feof($handle)) {
			$new_block = fread($handle, 32);
			$i = 0;
			if($new_block[$i]=="\xFF" && $new_block[$i+1]=="\xD8" && $new_block[$i+2]=="\xFF" && $new_block[$i+3]=="\xE0") {
				$i += 4;
				if($new_block[$i+2]=="\x4A" && $new_block[$i+3]=="\x46" && $new_block[$i+4]=="\x49" && $new_block[$i+5]=="\x46" && $new_block[$i+6]=="\x00") {
					// Read block size and skip ahead to begin cycling through blocks in search of SOF marker
					$block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
					$block_size = hexdec($block_size[1]);
					while(!feof($handle)) {
						$i += $block_size;
						$new_block .= fread($handle, $block_size);
						if($new_block[$i]=="\xFF") {
							// New block detected, check for SOF marker
							$sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
							if(in_array($new_block[$i+1], $sof_marker)) {
								// SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
								$size_data = $new_block[$i+2] . $new_block[$i+3] . $new_block[$i+4] . $new_block[$i+5] . $new_block[$i+6] . $new_block[$i+7] . $new_block[$i+8];
								$unpacked = unpack("H*", $size_data);
								$unpacked = $unpacked[1];
								$height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
								$width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
								return array($width, $height);
							} else {
								// Skip block marker and read block size
								$i += 2;
								$block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
								$block_size = hexdec($block_size[1]);
							}
						} else {
							return FALSE;
						}
					}
				}
			}
		}
		return FALSE;
	}

?>
