<?php
/* 
 * Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
 * Sources https://github.com/PSF1/pharinix
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * @category  PHP
 * @package   phpsysinfo
 * @author    Pedro Pelaez <aaaaa976@gmail.com>
 * @copyright 2015 Pharinix
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link      http://phpsysinfo.sourceforge.net
 */

/**
 * Ping Icons by Anastasya Bolshakova <https://www.iconfinder.com/nastu_bol>
 * @link https://www.iconfinder.com/iconsets/simple-files-1
 */

if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandPSIAgilmonSidebar")) {
    class commandPSIAgilmonSidebar extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            driverCommand::run('incRaphaelJS');
            driverCommand::run('incBSModal');
            driverCommand::run('incBS3Dialog');
            $path = driverCommand::getModPath('phpsysinfo');
            
            echo '<script src="'.CMS_DEFAULT_URL_BASE.$path.'js/psiAgilmonSidebar.js" type="text/javascript"></script>';
            
            $hosts = driverCommand::run('getNodes', array(
                'nodetype' => 'psihost',
                'fields' => 'title',
                'order' => '`title` ASC',
            ));
            
            echo '<div class="row">';
            echo '<div class="col-md-12">';
            
            echo '<legend>'.__('Hosts').'</legend>';
            echo '<input type="hidden" id="psiPingIcon0" value="'.CMS_DEFAULT_URL_BASE.$path.'assets/ping_0.png">';
            echo '<input type="hidden" id="psiPingIcon1" value="'.CMS_DEFAULT_URL_BASE.$path.'assets/ping_1.png">';
            echo '<input type="hidden" id="psiPingIcon2" value="'.CMS_DEFAULT_URL_BASE.$path.'assets/ping_2.png">';
            echo '<input type="hidden" id="psiPingIcon3" value="'.CMS_DEFAULT_URL_BASE.$path.'assets/ping_3.png">';
            echo '<input type="hidden" id="psiPingIcon4" value="'.CMS_DEFAULT_URL_BASE.$path.'assets/ping_4.png">';
            echo '<table class="table table-striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>#</th>';
            echo '<th>'.__('Host').'</th>';
            echo '<th>'.__('Actions').'</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody id="psiRemotes">';
            foreach($hosts as $id => $host) {
                echo '<tr>';
                echo '<td class="psiHostItemStatus_'.$id.' text-warning">'; // Status
                echo '<img src="'.CMS_DEFAULT_URL_BASE.$path.'assets/ping_ini.png"  data-toggle="tooltip" title="" width="16">';
                echo '';
                echo '</span>';
                echo '</td>';
                echo '<td>'; // Title
                echo '<span class="psiHostItem" data-id="'.$id.'">';
                echo $host['title'];
                echo '</span>';
                echo '</td>';
                echo '<td>'; // Actions
                
                // Show: PLAY: glyphicon glyphicon-eye-open STOP: glyphicon glyphicon-eye-close
                echo '<a href="#" id="cmdPsiHostItemPlay_'.$id.'" data-toggle="tooltip" title="'.__('Show/Hide in chart').'">';
                echo '<span class="glyphicon glyphicon-eye-close"></span>';
                echo '</a>&nbsp;';
                // Edit
                echo '<a href="#" id="cmdPsiHostItemEdit_'.$id.'" data-toggle="tooltip" title="'.__('Edit host information').'">';
                echo '<span class="glyphicon glyphicon-edit"></span>';
                echo '</a>&nbsp;';
                // Remove
                echo '<a href="#" id="cmdPsiHostItemRemove_'.$id.'" data-toggle="tooltip" title="'.__('Remove host').'">';
                echo '<span class="glyphicon glyphicon-remove"></span>';
                echo '</a>&nbsp;';
                
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            // Add new host
            echo '<a href="#" id="cmdPsiHostItemAdd" class="btn btn-sm btn-default" data-toggle="tooltip" title="'.__('Add new host').'">';
            echo '<span class="glyphicon glyphicon-plus-sign"></span>&nbsp;'.__('Add');
            echo '</a>';
            
            echo '</div>';
            echo '</div>';
            
            echo '<!-- Modal forms -->';
            echo '<div id="psiHostForm" class="modal fade" tabindex="-1" style="display: none;"></div>';
            echo '<div id="psiAlertModal" class="modal fade" tabindex="-1">';
            echo '<div class="modal-header">';
            echo '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
            echo '<h4 class="modal-title">'.__('Alert').'</h4>';
            echo '</div>';
            echo '<div class="modal-body">';
            echo '</div>';
            echo '<div class="modal-footer">';
            echo '<button type="button" data-default="modal" class="btn" data-dismiss="modal">'.__('Ok').'</button>';
            echo '</div>';
            echo '</div>';
            echo '<div id="psiConfirmModal" class="modal fade" tabindex="-1">';
            echo '<div class="modal-header">';
            echo '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
            echo '<h4 class="modal-title">'.__('Alert').'</h4>';
            echo '</div>';
            echo '<div class="modal-body">';
            echo '</div>';
            echo '<div class="modal-footer">';
            echo '<button type="button" data-default="modal" class="btn" data-dismiss="modal">'.__('Cancel').'</button>';
            echo '<button type="button" data-default="modal" class="btn btn-primary psiConfirmModalOk" data-dismiss="modal">'.__('Ok').'</button>';
            echo '</div>';
            echo '</div>';
        }

        public static function getHelp() {
            $path = driverCommand::run('modGetPath', array('name' => 'phpsysinfo'));
            $path = $path['path'];
            return array(
                "package" => 'phpsysinfo',
                "description" => __("Display host list control how HTML."), 
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
return new commandPSIAgilmonSidebar();
