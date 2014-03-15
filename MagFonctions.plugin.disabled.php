<?php
/*
@name MagFonctions
@author fran6t <trautmann@wse.fr>
@link http://blog.passion-tarn-et-garonne.info
@licence Ma Licence
@version 1.1.0
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
     * Si pas d'image > au minima ont returne le texte tronqué
     * Si l'image est au format portrait on retourne l'image et le texte tronqué
     * Si l'image est au format paysage on retourne uniquement l'image 
     *  
	 * @param string $text String dans laquelle chercher les images et le texte de présentation.
	 * @param integer $taille Taille minimale de l'image (pour exclure comme cela tout ce qui picto FB tweet...
	 * */
	public static function extractIMG($text, $taille = 200){
		$valretour = "";
		$letexte = "";
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
					// loading image into constructor
					$image = new FastImage($img_loc);
					list($width, $height) = $image->getSize();
					// echo "dimensions: " . $width . "x" . $height;					
					// Si superieur au mini requis on sort c'est ok
					if ($width > $taille || $height > $taille){
						// Si format paysage 
						if ($width > $height){
							// Si $width > 400 juste l'image pas de texte
							if ($width > 400){
								$lestyleimg = ' style="float:left;max-width:100%;max-height: auto;padding-right:1em;" ';
								$valretour = "<img src=\"".$img_loc."\"".$lestyleimg."/>";
							// L'image est petite nous plaçon l'image et le texte
							} else {
								$lestyleimg = ' style="float:left;max-width:200px;max-height: 240px;padding-right:1em;" ';
								//$valretour = "<img src=\"".$img_loc."\"".$lestyleimg."/>".FunctionsMag::txtCHAP($text);
								$valretour = "<img src=\"".$img_loc."\"".$lestyleimg."/>".stripTAG(TruncateHTML::truncateChars($text, '200', '...'));
							}
						// Nous sommes en format portrait nous plaçons l'image et le texte	
						} else {
							$lestyleimg = ' style="float:left;max-width:auto;max-height: 240px;padding-right:1em;" ';
							//$valretour = "<img src=\"".$img_loc."\"".$lestyleimg."/>".FunctionsMag::txtCHAP($text);
							$valretour = "<img src=\"".$img_loc."\"".$lestyleimg."/>".stripTAG(TruncateHTML::truncateChars($text, '200', '...'));
						}
						break;
					}
				}
			}
		}
		// Si pas d'image il faut que l'on force le texte
		if ( $valretour == "") { 
			//$valretour = FunctionsMag::txtCHAP($text);
			$valretour = stripTAG(TruncateHTML::truncateChars($text, '200', '...'));
		}
		return $valretour;
	}

} 


/* 
 * On enleve les tags qui peuvent ête génant dans pour l'affichage dans les bloc paire et impaire
 * 
 * */
	 
function stripTAG($text){
	//$text = truncateHtml($text, $length = 200, $ending = '...', $exact = false, $considerHtml = true);
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
 * FastImage - Because sometimes you just want the size!
 * Based on the Ruby Implementation by Steven Sykes (https://github.com/sdsykes/fastimage)
 *
 * Copyright (c) 2012 Tom Moor
 * Tom Moor, http://tommoor.com
 * (https://github.com/tommoor/fastimage/)
 *
 * MIT Licensed
 * @version 0.1
 */

class FastImage
{
	private $strpos = 0;
	private $str;
	private $uri;
	private $type;
	private $handle;
	
	public function __construct($uri = null)
	{
		if ($uri) $this->load($uri);
	}


	public function load($uri)
	{
		if ($this->handle) $this->close();
		
		$this->uri = $uri;
		$this->handle = fopen($uri, 'r');
	}


	public function close()
	{
		if ($this->handle) fclose($this->handle);
	}


	public function getSize()
	{
		$this->strpos = 0;
		if ($this->getType())
		{
			return array_values($this->parseSize());
		}
		
		return false;
	}


	public function getType()
	{
		$this->strpos = 0;
		
		if (!$this->type)
		{
			switch ($this->getChars(2))
			{
				case "BM":
					return $this->type = 'bmp';
				case "GI":
					return $this->type = 'gif';
				case chr(0xFF).chr(0xd8):
					return $this->type = 'jpeg';
				case chr(0x89).'P':
					return $this->type = 'png';
				default:
					return false;
			}
		}

		return $this->type;
	}


	private function parseSize()
	{	
		$this->strpos = 0;
		
		switch ($this->type)
		{
			case 'png':
				return $this->parseSizeForPNG();
			case 'gif':
				return $this->parseSizeForGIF();
			case 'bmp':
				return $this->parseSizeForBMP();
			case 'jpeg':
				return $this->parseSizeForJPEG();	    
		}
		
		return null;
	}


	private function parseSizeForPNG()
	{
		$chars = $this->getChars(25);

		return unpack("N*", substr($chars, 16, 8));
	}


	private function parseSizeForGIF()
	{
		$chars = $this->getChars(11);

		return unpack("S*", substr($chars, 6, 4));
	}


	private function parseSizeForBMP()
	{
		$chars = $this->getChars(29);
	 	$chars = substr($chars, 14, 14);
		$type = unpack('C', $chars);
		
		return (reset($type) == 40) ? unpack('L*', substr($chars, 4)) : unpack('L*', substr($chars, 4, 8));
	}


	private function parseSizeForJPEG()
	{
		$state = null;
		$i = 0;

		while (true)
		{
			switch ($state)
			{
				default:
					$this->getChars(2);
					$state = 'started';
					break;
					
				case 'started':
					$b = $this->getByte();
					if ($b === false) return false;
					
					$state = $b == 0xFF ? 'sof' : 'started';
					break;
					
				case 'sof':
					$b = $this->getByte();
					if (in_array($b, range(0xe0, 0xef)))
					{
						$state = 'skipframe';
					}
					elseif (in_array($b, array_merge(range(0xC0,0xC3), range(0xC5,0xC7), range(0xC9,0xCB), range(0xCD,0xCF))))
					{
						$state = 'readsize';
					}
					elseif ($b == 0xFF)
					{
						$state = 'sof';
					}
					else
					{
						$state = 'skipframe';
					}
					break;
					
				case 'skipframe':
					$skip = $this->readInt($this->getChars(2)) - 2;
					$state = 'doskip';
					break;
					
				case 'doskip':
					$this->getChars($skip);
					$state = 'started';
					break;
					
				case 'readsize':
					$c = $this->getChars(7);
			        
					return array($this->readInt(substr($c, 5, 2)), $this->readInt(substr($c, 3, 2)));
			}
		}
	}


	private function getChars($n)
	{
		$response = null;
		
		// do we need more data?		
		if ($this->strpos + $n -1 >= strlen($this->str))
		{
			$end = ($this->strpos + $n);

			while (strlen($this->str) < $end && $response !== false)
			{
				// read more from the file handle
				$need = $end - ftell($this->handle);

				if ($response = fread($this->handle, $need))
				{
					$this->str .= $response;
				}
				else
				{
					return false;
				}
			}	
		}
		
		$result = substr($this->str, $this->strpos, $n);
		$this->strpos += $n;
		
		// we are dealing with bytes here, so force the encoding
		//return mb_convert_encoding($result, "8BIT");
		return $result;
	}


	private function getByte()
	{
		$c = $this->getChars(1);
		$b = unpack("C", $c);
		
		return reset($b);
	}


	private function readInt($str)
	{
		$size = unpack("C*", $str);
		
	    	return ($size[1] << 8) + $size[2];
	}


	public function __destruct()
	{
		$this->close();
	}
}


/* *
 * Ci-dessous les fonctions ou class pour extraire une chaine de caractere en tenant 
 * compte des balises html
 * http://www.pjgalbraith.com/2011/11/truncating-text-html-with-php/
 * 
 * Exemple d'utilisation
 * $html = '<p>This is <strong>test</strong> html text.</p>';
 * $output = TruncateHTML::truncateChars($html, '11', '...');
 * echo $output;
 * $output = TruncateHTML::truncateWords($html, '3', '...');
 * echo $output;
* */


class TruncateHTML {
    
    public static function truncateChars($html, $limit, $ellipsis = '...') {
        
        if($limit <= 0 || $limit >= strlen(strip_tags($html)))
            return $html;
        
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        
        $body = $dom->getElementsByTagName("body")->item(0);
        
        $it = new DOMLettersIterator($body);
        
        foreach($it as $letter) {
            if($it->key() >= $limit) {
                $currentText = $it->currentTextPosition();
                $currentText[0]->nodeValue = substr($currentText[0]->nodeValue, 0, $currentText[1] + 1);
                self::removeProceedingNodes($currentText[0], $body);
                self::insertEllipsis($currentText[0], $ellipsis);
                break;
            }
        }
        
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }
    
    public static function truncateWords($html, $limit, $ellipsis = '...') {
        
        if($limit <= 0 || $limit >= self::countWords(strip_tags($html)))
            return $html;
        
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        
        $body = $dom->getElementsByTagName("body")->item(0);
        
        $it = new DOMWordsIterator($body);
        
        foreach($it as $word) {            
            if($it->key() >= $limit) {
                $currentWordPosition = $it->currentWordPosition();
                $curNode = $currentWordPosition[0];
                $offset = $currentWordPosition[1];
                $words = $currentWordPosition[2];
                
                $curNode->nodeValue = substr($curNode->nodeValue, 0, $words[$offset][1] + strlen($words[$offset][0]));
                
                self::removeProceedingNodes($curNode, $body);
                self::insertEllipsis($curNode, $ellipsis);
                break;
            }
        }
        
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }
    
    private static function removeProceedingNodes(DOMNode $domNode, DOMNode $topNode) {        
        $nextNode = $domNode->nextSibling;
        
        if($nextNode !== NULL) {
            self::removeProceedingNodes($nextNode, $topNode);
            $domNode->parentNode->removeChild($nextNode);
        } else {
            //scan upwards till we find a sibling
            $curNode = $domNode->parentNode;
            while($curNode !== $topNode) {
                if($curNode->nextSibling !== NULL) {
                    $curNode = $curNode->nextSibling;
                    self::removeProceedingNodes($curNode, $topNode);
                    $curNode->parentNode->removeChild($curNode);
                    break;
                }
                $curNode = $curNode->parentNode;
            }
        }
    }
    
    private static function insertEllipsis(DOMNode $domNode, $ellipsis) {    
        $avoid = array('a', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'a', 'img', 'iframe'); //html tags to avoid appending the ellipsis to
        
        if( in_array($domNode->parentNode->nodeName, $avoid) && $domNode->parentNode->parentNode !== NULL) {
            // Append as text node to parent instead
            $textNode = new DOMText($ellipsis);
            
            if($domNode->parentNode->parentNode->nextSibling)
                $domNode->parentNode->parentNode->insertBefore($textNode, $domNode->parentNode->parentNode->nextSibling);
            else
                $domNode->parentNode->parentNode->appendChild($textNode);
        } else {
            // Append to current node
            $domNode->nodeValue = rtrim($domNode->nodeValue).$ellipsis;
        }
    }
    
    private static function countWords($text) {
        $words = preg_split("/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY);
        return count($words);
    }
    
}




/**
 * Iterates individual characters (Unicode codepoints) of DOM text and CDATA nodes
 * while keeping track of their position in the document.
 *
 * Example:
 *
 *  $doc = new DOMDocument();
 *  $doc->load('example.xml');
 *  foreach(new DOMLettersIterator($doc) as $letter) echo $letter;
 *
 * NB: If you only need characters without their position
 *     in the document, use DOMNode->textContent instead.
 *
 * @author porneL http://pornel.net
 * @license Public Domain
 *
 */
final class DOMLettersIterator implements Iterator
{
    private $start, $current;
    private $offset, $key, $letters;

    /**
     * expects DOMElement or DOMDocument (see DOMDocument::load and DOMDocument::loadHTML)
     */
    function __construct(DOMNode $el)
    {
        if ($el instanceof DOMDocument) $this->start = $el->documentElement;
        else if ($el instanceof DOMElement) $this->start = $el;
        else throw new InvalidArgumentException("Invalid arguments, expected DOMElement or DOMDocument");
    }

    /**
     * Returns position in text as DOMText node and character offset.
     * (it's NOT a byte offset, you must use mb_substr() or similar to use this offset properly).
     * node may be NULL if iterator has finished.
     *
     * @return array
     */
    function currentTextPosition()
    {
        return array($this->current, $this->offset);
    }

    /**
     * Returns DOMElement that is currently being iterated or NULL if iterator has finished.
     *
     * @return DOMElement
     */
    function currentElement()
    {
        return $this->current ? $this->current->parentNode : NULL;
    }

    // Implementation of Iterator interface
    function key()
    {
        return $this->key;
    }

    function next()
    {
        if (!$this->current) return;

        if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE)
        {
            if ($this->offset == -1)
            {
                // fastest way to get individual Unicode chars and does not require mb_* functions
                preg_match_all('/./us',$this->current->textContent,$m); $this->letters = $m[0];
            }
            $this->offset++; $this->key++;
            if ($this->offset < count($this->letters)) return;
            $this->offset = -1;
        }

        while($this->current->nodeType == XML_ELEMENT_NODE && $this->current->firstChild)
        {
            $this->current = $this->current->firstChild;
            if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE) return $this->next();
        }

        while(!$this->current->nextSibling && $this->current->parentNode)
        {
            $this->current = $this->current->parentNode;
            if ($this->current === $this->start) {$this->current = NULL; return;}
        }

        $this->current = $this->current->nextSibling;

        return $this->next();
    }

    function current()
    {
        if ($this->current) return $this->letters[$this->offset];
        return NULL;
    }

    function valid()
    {
        return !!$this->current;
    }

    function rewind()
    {
        $this->offset = -1; $this->letters = array();
        $this->current = $this->start;
        $this->next();
    }
}




?>
