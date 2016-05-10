<?php
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandPsiAddHostForm")) {
    class commandPsiAddHostForm extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            ?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title"><?php __e('Add host');?></h4>
</div>
<?php
//            $grupos = driverCommand::run("getNodes", array("nodetype" => "group"));
            $path = driverCommand::getModPath('phpsysinfo');
            $fid = driverCommand::run("newID");
            $fid = "frm".str_replace(".", "", $fid["id"]);
            
//            driverCommand::run('incDualListbox');
            driverCommand::run('incFormValidator');
            ?>
<div class="modal-body">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab"><?php __e('General');?></a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="tab1">
                    <form class="form-horizontal" id="<?php echo $fid;?>">
                    <fieldset>
                        <p>&nbsp;</p>
                    <!-- Text input-->
                    <div class="form-group">
                      <label class="col-md-4 control-label" for="txtLabel"><?php __e('Label');?></label>  
                      <div class="col-md-8">
                      <input id="txtLabel" name="txtLabel" 
                             type="text" 
                             placeholder="<?php __e('Label');?>" 
                             class="form-control input-md" required="">
                      </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                      <label class="col-md-4 control-label" for="txtURL"><?php __e('URL');?></label>  
                      <div class="col-md-8">
                      <input id="txtURL" name="txtURL"
                             type="text" 
                             placeholder="<?php __e('http://www.example.com/');?>" 
                             class="form-control input-md" required="">
                      </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                      <label class="col-md-4 control-label" for="txtUser"><?php __e('User');?></label>  
                      <div class="col-md-8">
                      <input id="txtUser" name="txtUser"
                             type="text" 
                             placeholder="<?php __e('User');?>" 
                             class="form-control input-md">
                      </div>
                    </div>

                    <!-- Password input-->
                    <div class="form-group">
                      <label class="col-md-4 control-label" for="txtPass1"><?php __e('Password');?></label>
                      <div class="col-md-8">
                        <input id="txtPass1" name="txtPass1"
                             type="password" 
                             placeholder="<?php __e('Password');?>" 
                             class="form-control input-md">
                        <span class="help-block"><?php __e('The password will be encripted.');?></span>
                      </div>
                    </div>

                    </fieldset>
                    </form>
                </div>
	</div>
</div>
<div class="modal-footer">
	<button type="button" data-dismiss="modal" class="btn"><?php __e('Cancel');?></button>
	<button type="button" class="btn btn-success" id="cmdSave-<?php echo $fid;?>"><?php __e('Save');?></button>
            <?php
            echo "<script>". str_replace("[frmid]", $fid,file_get_contents($path.'js/psiAddHostForm.js'))."</script>";
        echo "</div>";
        }

        public static function getHelp() {
            return array(
                "package" => "phpsysinfo",
                "description" => __("Show a add host form."), 
                "parameters" => array(), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(), 
                    "response" => array(),
                ),
                "echo" => true
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
        public static function getAccessFlags() {
            return driverUser::PERMISSION_FILE_GROUP_EXECUTE;
        }
        
        public static function getAccessData($path = "") {
            $me = __FILE__;
            $resp = parent::getAccessData($me);
            if ($resp["group"] == 0) {
                $defGroup = driverConfig::getCFG()->getSection('[phpsysinfo]')->get('default_group');
                $sql = "select `id` from `node_group` where `title` = '$defGroup'";
                $q = dbConn::Execute($sql);
                if (!$q->EOF) {
                    $resp["group"] = $q->fields["id"];
                }
            }
            return $resp;
        }
    }
}
return new commandPsiAddHostForm();