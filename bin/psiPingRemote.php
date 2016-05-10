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
 * @copyright 2016 Pharinix
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link      http://phpsysinfo.sourceforge.net
 */

if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandPSIPingRemote")) {
    class commandPSIPingRemote extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'hostid' => 0,
            ), $params);
//            header('Access-Control-Allow-Origin: *');
             /**
             * application root path
             *
             * @var string
             */
            $path = driverCommand::getModPath('phpsysinfo');
            include_once $path.'drivers/psiTools.php';
            
            $host = driverCommand::run('getNode', array(
                'nodetype' => 'psihost',
                'node' => $params['hostid'],
            ));
            if (empty($host)) {
                return array('ok' => false, 'msg' => __('Host not found'));
            }
            $host = $host[$params['hostid']];
//            $pass = driverPSITools::decriptPass($host['pass']);
            // Call to remote API
            $start = microtime(true);
            $ping = driverPSITools::apiCall($host['url'], array(
                'command' => 'nothing',
                'interface' => 'nothing',
            ),true, false, null, 10);
            if ($ping['request']['http_code'] != 200) {
                return array('ok' => false, 'msg' => __('Connection error.'));
            }
            return array('ok' => true, 'secs' => microtime(true) - $start);
        }

        public static function getHelp() {
            $path = driverCommand::run('modGetPath', array('name' => 'phpsysinfo'));
            $path = $path['path'].'drivers/phpsysinfo.ini';
            return array(
                "package" => 'phpsysinfo',
                "description" => __("Do a ping to a remote host."), 
                "parameters" => array(
                        "hostid" => __('Local PSI Host ID.'),
                    ), 
                "response" => array(
                    "secs" => __('Query delay in seconds'),
                ),
                "type" => array(
                    "parameters" => array(
                        "hostid" => "integer",
                    ), 
                    "response" => array(
                        "secs" => "integer",
                    ),
                ),
                "echo" => false
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
return new commandPSIPingRemote();
