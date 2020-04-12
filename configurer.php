<?php
define("LINE_BREAK","</br>");
$configuration = json_decode(file_get_contents('backuper.json'));
if(isset($_POST["add_project"])){
    addProject($configuration);
}
elseif(isset($_POST["remove_project"])){
    removeProject($configuration);
}
elseif(isset($_POST["update_selected_status"])){
    updateSelectedStatus($configuration);
}
elseif(isset($_POST["update_logging_status"])){
    updateLoggingStatus($configuration);
}
elseif(isset($_POST["save_backup_path"])){
    saveBackupPath($configuration);
}

echo importsHTML();
echo buildEntryViewHTML($configuration).LINE_BREAK.buildTableHTML($configuration);


function addProject($configuration){
    $new_project = new \StdClass;
    $new_project->name = $_POST["project_name"];
    $new_project->path = $_POST["path"];
    $new_project->is_selected = isset($_POST["selected"]);
    $configuration->project_paths[] = $new_project;
    updateConfiguration($configuration);
}
function removeProject($configuration){
    $index = $_POST["index"];
    unset($configuration->project_paths[$index]);
    updateConfiguration($configuration);
}
function updateSelectedStatus($configuration){
    $index = $_POST["index"];
    $configuration->project_paths[$index]->is_selected = isset($_POST["selected"]);
    updateConfiguration($configuration);
}
function updateLoggingStatus($configuration){
    $configuration->enable_logging = isset($_POST["enable_logging"]);
    updateConfiguration($configuration);
}
function saveBackupPath($configuration){
    $configuration->backup_path = $_POST["project_path"];
    updateConfiguration($configuration);
}

function updateConfiguration($configuration){
    file_put_contents('backuper.json',json_encode($configuration));
}
function importsHTML(){
    return "
  <title>Backuper</title>
  <meta charset=\"utf-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css\"> 
  <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js\"></script>
  <script src=\"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js\"></script>
  <script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js\"></script>"." 

";
}
function buildTableHTML($configuration){
    $string = "<div class='col-sm-8'><table class='table table-hover'><tr><th>S.No</th><th>Project Name</th><th>Path</th><th>Selected</th></tr>";
    $sno=1;
    foreach ($configuration->project_paths as $config){
        $select_element = "
        <form action='' method='post'>
            <input type='checkbox' onchange='this.form.submit()' name='selected' value='".($sno-1)."' ".($config->is_selected?'checked':'').">
            <input type='hidden' name='update_selected_status'>
            <input type='hidden' name='index' value='".($sno-1)."'>
        </form>";
        $remove_btn_element = "
        <form action='' method='post'>
            <input type='hidden' name='index' value='".($sno-1)."'>
            <input type='hidden' name='remove_project'>
            <button type=\"submit\" class=\"btn btn-danger\">Remove</button>
        </form>
        ";
        $string .= "<tr><td>$sno</td><td>$config->name</td><td>$config->path</td><td>$select_element</td><td>$remove_btn_element</td></tr>";
        $sno++;
    }
    $string .= "</table></div>";
    return $string;
}
function buildEntryViewHTML($configuration){
    $string = "
    <style>
    </style>
    <div class=\"container\">
      <div class=\"row\">
        <div class=\"col-sm-4 mt-3\">
        <form action='' method='post'>
           <div class=\"form-group\">
              <label for=\"comment\">Project Name</label>
              <input type=\"text\" class=\"form-control\" name=\"project_name\">
           </div>
           <div class=\"form-group\">
              <label for=\"comment\">Path</label>
              <input type=\"text\" class=\"form-control\" name=\"path\">
           </div>
            <div class=\"form-group\">
             <input type='checkbox' id='selected' name='selected'>
             <label for=\"selected\">Selected</label>
           </div>
           
            <button type=\"submit\" name='add_project' class=\"btn btn-success\">Add</button></br>
        </form>
    </div>
    <div class=\"col-sm-4 mt-3\">
        <form action='praveen_backuper.php' method='post'>
            <button type=\"submit\" name='backup_from_configurer' class=\"btn btn-primary btn-lg\">Backup Now</button>
        </form>
        <form action='' method='post'>
             <input type=\"checkbox\" name='enable_logging' onchange='this.form.submit()' ".($configuration->enable_logging?'checked':'')." class=\"btn btn-primary btn-lg\"/>
             <input type=\"hidden\" name='update_logging_status'/>
             <label for=\"comment\">Enable Logging</label>
        </form>
    </div>
    <div class=\"col-sm-1 mt-3\"></div>
    <div class=\"col-sm-3 mt-3\">
        <form action='' method='post'>
           <div class=\"form-group\">
              <label for=\"comment\">Backup path</label>
              <input type=\"text\" value='".$configuration->backup_path."' class=\"form-control\" name=\"project_path\">
           </div>
           <button type=\"submit\" name='save_backup_path' class=\"btn btn-success\">Save</button>
        </form>
    </div>
  </div>";
    return $string;
}