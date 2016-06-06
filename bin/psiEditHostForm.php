<?php
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandsiEditHostForm")) {
    class commandsiEditHostForm extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "id" => 0,
            ), $params);
            $path = driverCommand::getModPath('phpsysinfo');
            
            ?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title"><?php __e('Edit host');?></h4>
</div>
<?php
        $host = driverNodes::getNodes(array(
                "nodetype" => 'psihost',
                "where" => "`id` = ".$params["id"],
            ), false);
        if (count($host) == 0) {
            echo driverCommand::getAlert(__("Host not found..."));
        } else {
            //$grupos = driverCommand::run("getNodes", array("nodetype" => "group"));
            $grupos = driverNodes::getNodes(array("nodetype" => "group"), false);
            $fid = driverCommand::run("newID");
            $fid = "frm".str_replace(".", "", $fid["id"]);
            driverCommand::run('incDualListbox');
            driverCommand::run('incFormValidator');
            // Default tabs
            $tabs = array(
                'tabGeneral' => array(
                    'active' => true,
                    'label' => __('General'),
                    'html_form' => '<form class="form-horizontal" id="'.$fid.'" host="'.$params["id"].'">'.
                    '<fieldset>'.
                        '<p>&nbsp;</p>'.
                        '<!-- Text input-->'.
                        '<div class="form-group">'.
                        '<label class="col-md-4 control-label" for="txtLabel">'.__('Label').'</label>'.
                        '<div class="col-md-8">'.
                        '<input id="txtLabel" name="txtLabel" '.
                            'value ="'.$host[$params["id"]]["title"].'" '.
                            'type="text" '.
                            'placeholder="'.__('Label').'" '.
                            'class="form-control input-md" required="">'.
                        '</div>'.
                    '</div>'.
                    '<!-- Text input-->'.
                    '<div class="form-group">'.
                        '<label class="col-md-4 control-label" for="txtURL">'.__('URL').'</label>'.
                    '<div class="col-md-8">'.
                        '<input id="txtURL" name="txtURL" '.
                            'value ="'.$host[$params["id"]]["url"].'" '.
                            'type="text" '.
                            'placeholder="'.__('http://www.example.com/').'"'.
                            'class="form-control input-md" required="">'.
                    '</div>'.
                    '</div>'.
                    '<!-- Text input-->'.
                    '<div class="form-group">'.
                        '<label class="col-md-4 control-label" for="txtUser">'.__('User').'</label>'.
                        '<div class="col-md-8">'.
                        '<input id="txtUser" name="txtUser" '.
                            'value ="'.$host[$params["id"]]["user"].'" '.
                            'type="text" '.
                            'placeholder="'.__('User').'" '.
                            'class="form-control input-md">'.
                        '</div>'.
                    '</div>'.
                    '<!-- Password input-->'.
                    '<div class="form-group">'.
                        '<label class="col-md-4 control-label" for="txtPass1">'.__('Password').'</label>'.
                        '<div class="col-md-8">'.
                            '<input id="txtPass1" name="txtPass1" '.
                                'type="password" '.
                                'placeholder="'.__('Password').'" '.
                                'class="form-control input-md">'.
                            '<span class="help-block">'.__('The password will be encripted.').'</span>'.
                        '</div>'.
                    '</div>'.
                    '</fieldset>'.
                    '</form>',
                )
            );
            // Allow override and expand form tabs
            driverHook::CallHook('psiEditHostFormTabsHook', array(
                'tabs' => &$tabs,
                'host' => &$host[$params["id"]],
                'formId' => $fid,
            ));
            // We verify that we only have one default tab
            $tabActive = '';
            // Get first active tab
            foreach($tabs as $tabId => $tabInfo) {
                if ($tabInfo['active']) {
                    $tabActive = $tabId;
                    break;
                }
            }
            // We ensure that we have only one active tab
            foreach($tabs as $tabId => $tabInfo) {
                if ($tabId == $tabActive) {
                    $tabInfo['active'] = true;
                } else {
                    $tabInfo['active'] = false;
                }
            }
            echo '<div class="modal-body">'."\n";
            echo '<ul class="nav nav-tabs">'."\n";
            foreach($tabs as $tabId => $tabInfo) {
                echo '<li class="'.($tabInfo['active']?'active':'').'"><a href="#'.$tabId.'" data-toggle="tab">'.$tabInfo['label'].'</a></li>'."\n";
            }
            echo '</ul>'."\n";
            
            echo '<div class="tab-content">'."\n";
            foreach($tabs as $tabId => $tabInfo) {
                echo '<div class="tab-pane '.($tabInfo['active']?'active':'').'" id="'.$tabId.'">'."\n";
                echo $tabInfo['html_form']."\n";
                echo '</div>'."\n";
            }
            echo '</div>'."\n";
            echo '</div>'."\n";
        }
?>
<div class="modal-footer">
	<button type="button" data-dismiss="modal" class="btn"><?php __e('Cancel');?></button>
<?php
        if ($params["id"] != 0) {
?>
	<button type="button" class="btn btn-success" id="cmdSave-<?php echo $fid;?>"><?php __e('Save'); ?></button>
            <?php
            $js = file_get_contents($path."js/psiEditHostForm.js");
            echo "<script>".str_replace("[frmid]", $fid, $js)."</script>";
        }
        echo "</div>";
        }

        public static function getHelp() {
            return array(
                "package" => "phpsysinfo",
                "description" => __("Show edit host form."), 
                "parameters" => array(
                    "id" => __("Host ID to edit.")
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "id" => "integer"
                    ), 
                    "response" => array(),
                ),
                "echo" => true,
                "hooks" => array(
                        array(
                            "name" => "psiEditHostFormTabsHook",
                            "description" => __("Allow change the host edit form."),
                            "parameters" => array(
                                'tabs' => __("Tab definition array. array(<tab ID> => array('active' => <It's the default active tab>, 'label' => <tab label>, 'html_form' => <HTML form, without the form tag>))"),
                                'host' => __("Host object"),
                                'formId' => __("Form unique ID"),
                            )
                        )
                    )
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
return new commandsiEditHostForm();