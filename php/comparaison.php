<?php
						/***********************************************
						***************READING DIRECTORY****************
						************************************************/


function directoryReading($pathDir, $file){
	//tạo file để save dữ liệu
	$fileOpen = fopen($file,'a'); //ouvrir le fichier en mode ecriture et creer le fichier en cas de non-existe
	chmod("$pathDir", 0777); //Lecture et ecriture pour proprietaire, lecture pour les autres
	//mở thư mục cần đọc
	$dir = opendir($pathDir);
	while(($fichier = readdir($dir)) !== false){ //đọc nội dung của thư mục
		if($fichier != '.' && $fichier != '..' && $fichier != '.DS_Store'){	
			if (is_dir(($folder = $pathDir.'/'.$fichier))){ //nễu đường dẫn là thư mục 
				directoryReading($pathDir.'/'.$fichier, $file);
			} else {//nếu đường dẫn là file thông thường
				fwrite($fileOpen, $pathDir."/".$fichier.'; '
								.md5_file($pathDir.'/'.$fichier).'; '
								.filemtime($pathDir.'/'.$fichier)."\n");
			}
		}
	}
	closedir($dir);
	fclose($fileOpen);
}

//renvoyer un string qui sera le nom du fichier 
function savingInfo($directoryPath){
	$subfix = time();
	$handle = '../backupFilePath/fileSaving'.$subfix.'.txt';
	directoryReading($directoryPath,$handle); 
	//echo 'READING COMPLETE!!!!';
	return($handle);
}

						/****************************************************
						***************COMPARE 2 BACKUP FILES****************
						*****************************************************/



function compare($fileAssured, $fileTemp){
	//tạo array để save dữ liệu
	$arrayModif = array();
	$arrayUnknown = array();
	$arrayLost = array();
	
	chmod("$fileAssured",0777);
	chmod("$fileTemp",0777);
	//file quet goc
	$tabfich1=file($fileAssured); 
	//file quet tam thoi
	$tabfich2=file($fileTemp);
	
	for( $i = 0 ; $i < count($tabfich1) ; $i++ ) {
		$mot_original = explode(';', $tabfich1[$i]);
		$lost = 1;
		for ($j = 0; $j < count($tabfich2) ; $j++){
			$mot = explode(';', $tabfich2[$j]);
			//echo $mot[0];
			if(strcmp ($mot_original[0], $mot[0]) == 0){
				$lost = 0;
				//un fichier est bien verifie
				if (strcmp ($mot_original[1],$mot[1])==0 && strcmp ($mot_original[2],$mot[2])==0){
					//echo 'file '.$mot_original[0].' is OK :)';
				} else { //un fichier est modifie
					//echo ('file '.$mot_original[0].' is changed');
					array_push($arrayModif, $mot[0]);
				}
				break;
			} 
		}

		//si un fichier est efface
		if ($lost){
			array_push($arrayLost, $mot_original[0]);
		}
	}
	
	//si un fichier est ajoute sans savoir
	if (count($tabfich1) < count($tabfich2) - count($arrayLost)){
		for( $i = 0 ; $i < count($tabfich2) ; $i++ ) {
			$ajoute = 1;
			$mot = explode(';', $tabfich2[$i]);
			for ($j = 0; $j < count($tabfich1) ; $j++){
				$mot_original = explode(';', $tabfich1[$j]);
				if(strcmp ($mot_original[0], $mot[0]) == 0){
					$ajoute = 0;
					break;
				} 
			}
			if ($ajoute==1){
				array_push($arrayUnknown, $mot[0]);
			}
		}
	}

	//autre erreur
	if (count($tabfich1) != (count($tabfich2) + count($arrayLost) - count($arrayUnknown))){
		echo "Erreur a cause de la dublication de fichier ou quelque chose d'autre";
	} 

	echo '<pre> Fichier change: ';
	print_r($arrayModif); echo '<br>'; echo '<br>';
	echo '</pre>';
	echo '<pre> fichier ajoute: ';
	print_r($arrayUnknown); echo '<br>'; echo '<br>';
	echo '<pre> fichier perdu: ';
	print_r($arrayLost); echo '<br>';
	echo '</pre>';
}

//test
//compare("../backupFilePath/original.txt","../fileSaving1438587553.txt");
//echo savingInfo("..");
								/********************************************
								***************MAIN PROGRAMME****************
								*********************************************/

//separer en plusieurs dossiers
function scan($parametre){
	$imagesArray = array("../images");
	$codeArray = array("../css", "../html", "../js");
	$tous = array("..");
	switch ($parametre){ // on indique sur quelle variable on travaille
	    case 0: 
	       for ($j = 0; $j < count($codeArray) ; $j++){
	       		$savingFilePath = savingInfo($codeArray[$j]);
	       }
	       compare("../backupFilePath/original/original_code.txt",$savingFilePath);
	    break;
	    case 1: 
	       for ($j = 0; $j < count($imagesArray) ; $j++){
	       		$savingFilePath = savingInfo($imagesArray[$j]);
	       		//compare("../backupFilePath/original.txt",$savingFilePath);
	       }
	       compare("../backupFilePath/original/original_img.txt",$savingFilePath);
	    break;
	    case 2: 
	       compare("../backupFilePath/original/original_tous.txt",savingInfo(".."));
	    break;
	}
}

								/********************************************
								********************TEST*********************
								*********************************************/

//scan(0); //scan code folder
//scan(1); //scan images folde
//scan(2); //scan all

if (isset($_POST['Code']) && !isset($_POST['Images']) && !isset($_POST['Tous']) && $_POST['Code'] == "on"){
	echo 'Ban da chon quet "Code". Day la ket qua sau khi quet:';
	scan(0);
} else if (!isset($_POST['Code']) && isset($_POST['Images']) && !isset($_POST['Tous']) && $_POST['Images'] == "on"){
	echo 'Ban da chon quet "Images". Day la ket qua sau khi quet:';
	scan(1);
}else if (!isset($_POST['Code']) && !isset($_POST['Images']) && isset($_POST['Tous']) && $_POST['Tous'] == "on"){
	echo 'Ban da chon quet "Tat ca". Day la ket qua sau khi quet:';
	scan(2);
}else 
?>
