<?php
//echo __DIR__;exit;
$from_console = isset($_POST["backup_from_configurer"]);
date_default_timezone_set("Asia/Calcutta");
$configurations = json_decode(file_get_contents('backuper.json'));
$paths = $configurations->project_paths;
validateConfiguration($configurations);
define('BACKUP_PATH',$configurations->backup_path);
define('LOGGING',$configurations->enable_logging);
$sno = 0;
foreach($paths as $path){
    if($path->is_selected){
	    generateZIP($path);
		$sno++;
	}
}
echo $sno.' Projects backuped successfully';
if($from_console){
    header('Location:configurer.php');
}


function generateZIP($project){
    $path = $project->path;
    $project_folder_name = getFolderNameFromPath($path);
	$rootPath = realpath("$path/");

	$zip = new ZipArchive();
	$zipFileName = BACKUP_PATH.$project_folder_name.'-'.date('Y-m-d h-i A').'.zip';
	$zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	$db_file_name = date('Y-m-d H i s').".sql";
	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($rootPath),
		RecursiveIteratorIterator::LEAVES_ONLY
	);
	
	
	foreach($files as $name => $file){
		if (!$file->isDir()) {
			$filePath = $file->getRealPath();
			$relativePath = substr($filePath, strlen($rootPath) + 1);
            //var_dump($relativePath);
			$zip->addFile($filePath, $relativePath);
		}
	}
	//unlink($db_file_name);
	// Zip archive will be created only after closing object
	$zip->close();
	if(LOGGING)
        file_put_contents('backup_log',date('Y-m-d H:i:s').' - '.$project->name.';'.$project->path.PHP_EOL,FILE_APPEND);


//	unlink($db_file_name);
}
function getFolderNameFromPath($path){
    $project_folder_name = $path;
    if (strpos($path, '/') !== false) {
        $project_folder_name = explode('/',$path);
        $project_folder_name = $project_folder_name[count($project_folder_name)-1];
    }
    elseif (strpos($path, '\\') !== false){
        $project_folder_name = explode('\\',$path);
        $project_folder_name = $project_folder_name[count($project_folder_name)-1];
    }
    return $project_folder_name;
}
function validateConfiguration($configurations){
    if($configurations->backup_path == "" or count($configurations->project_paths) <= 0){
        if($configurations->backup_path == "")
            file_put_contents('backup_log',date('Y-m-d H:i:s').' - '.'Error. No Backup path Specified'.PHP_EOL,FILE_APPEND);
        if(count($configurations->project_paths) <= 0)
            file_put_contents('backup_log',date('Y-m-d H:i:s').' - '.'Error. Project is empty'.PHP_EOL,FILE_APPEND);
		
		echo '</br>Error in Running projects <a href="configurer.php">Configure Here</a>';
		
        exit;
    }

}