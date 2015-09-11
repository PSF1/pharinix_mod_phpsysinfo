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

if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandPSIBlock")) {
    class commandPSIBlock extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $path = driverCommand::run('modGetPath', array(
                'name' => 'raphael_js'
            ));
            $path = $path['path'];
            if ($path == '') {
                return array('ok' => false, 'msg' => __("Module 'raphael_js' is required."));
            }
            echo '<link href="'.CMS_DEFAULT_URL_BASE.$path.'morrisjs/morris.css" rel="stylesheet">';
            echo '<script src="'.CMS_DEFAULT_URL_BASE.$path.'raphael-min.js" type="text/javascript"></script>';
            echo '<script src="'.CMS_DEFAULT_URL_BASE.$path.'morrisjs/morris.min.js" type="text/javascript"></script>';
            $path = driverCommand::run('modGetPath', array(
                'name' => 'phpsysinfo'
            ));
            $path = $path['path'];
            echo '<script src="'.CMS_DEFAULT_URL_BASE.$path.'js/psiBlock.js" type="text/javascript"></script>';
            
            echo '<div class="row">';
            echo '<div class="col-md-12">';
            
            echo '<legend>'.__('Server status').'</legend>';
            echo '<div id="psiBlock">'.__('Please wait').'</div>';
            echo '<h6>';
            echo '<a href="http://phpsysinfo.github.io/phpsysinfo/" target="_blank">'.__('phpSysInfo project').'</a> · ';
            echo '<a href="https://github.com/PSF1/pharinix_mod_phpsysinfo" target="_blank">'.__('Pharinix phpSysInfo module').'</a>';
            echo '</h6>';
            
            echo '<legend>'.__('Remote servers').'</legend>';
            echo '<div>';
            
            echo '</div>';
            echo '<table class="table table-striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>#</th>';
            echo '<th>'.__('Host').'</th>';
            echo '<th>'.__('CPU Load').'</th>';
            echo '<th>'.__('RAM').' - '.__('used').' / '.__('total').'</th>';
            echo '<th>'.__('SWAP').' - '.__('used').' / '.__('total').'</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody id="psiRemotes">';
        
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
        }

        public static function getHelp() {
            $path = driverCommand::run('modGetPath', array('name' => 'phpsysinfo'));
            $path = $path['path'];
            return array(
                "package" => 'phpsysinfo',
                "description" => __("Display server information how HTML."), 
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
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
    }
}
return new commandPSIBlock();
