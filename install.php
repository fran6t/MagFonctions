<?php
/* 
	* Nous avons besoins de deux tables :
	* 
	* La table MagFontions_param :
	* qui doit permettre de faire connaitre 
	* entre autre le tag permettant d'aller chercher une image representative
	* d'un billet sur le site concerné par un flux exemple le site pressecitron.net
	* fournit un flux expurgé des images comme il s'agit d'un wordpress il va suffir 
	* de renseigner que l'image fait partie d'une dis ayant la class post-content et 
	* nous extrayons l'image d'illustration de cette partie
	* 
	* La table MagFonctions_cache :
	* il s'agit de ne pas faire de recherche de taille d'image sur un site distant 
	* dès lors que cette recherche a déjà été faite une fois. 
*/	

	mysql_query('			
		CREATE TABLE IF NOT EXISTS `'.MYSQL_PREFIX.'MagFonctions_cache` (
			`id` int(11) NOT NULL,
			`feed` int(11) NOT NULL,
			`urlimage` char(250) CHARACTER SET utf8 NOT NULL,
			`imgwidth` int(11) NOT NULL,
			`imgheight` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	');



	mysql_query('
		CREATE TABLE IF NOT EXISTS `'.MYSQL_PREFIX.'Fonctions_param` (
			`id` int(11) NOT NULL,
			`tag` varchar(50) CHARACTER SET utf8 NOT NULL,
			`idouclass` varchar(50) CHARACTER SET utf8 NOT NULL,
			`validouclass` varchar(50) CHARACTER SET utf8 NOT NULL,
			`website` text CHARACTER SET utf8 NOT NULL,
			`Remarque` varchar(250) CHARACTER SET utf8 NOT NULL,
			UNIQUE KEY `id` (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	');

?>
