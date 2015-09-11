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

if (!class_exists("commandPSIInfo")) {
    class commandPSIInfo extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'plugin' => '',
                'filters' => '',
            ), $params);
            header('Access-Control-Allow-Origin: *');
             /**
             * application root path
             *
             * @var string
             */
            $path = driverCommand::run('modGetPath', array(
                'name' => 'phpsysinfo'
            ));
            $path = $path['path'];
            if ($path == '') {
                return array('ok' => false, 'msg' => __('Uh!! What??'));
            }
            if (!defined('APP_ROOT')) define('APP_ROOT', $path.'drivers/');

            /**
             * internal xml or external
             * external is needed when running in static mode
             *
             * @var boolean
             */
            if (!defined('PSI_INTERNAL_XML')) define('PSI_INTERNAL_XML', true);

            require_once APP_ROOT.'/includes/autoloader.inc.php';

            // check what xml part should be generated
            if ($params['plugin'] != '') {
                $plugin = basename(htmlspecialchars($params['plugin']));
                if ($plugin == "complete") {
                    $output = new WebpageStdClass(true, null);
                } elseif ($plugin != "") {
                    $output = new WebpageStdClass(false, $plugin);
                } else {
                    unset($output);
                }
            } else {
                $output = new WebpageStdClass(false, null);
            }
            if (!$output->existError()) {
//                include_once 'usr/xml2array/xml2array.php';
//                $respXML = $output->getXMLString();
//                $resp = xml_string_to_array($respXML);
//                return $resp;
                $filters = null;
                if ($params['filters'] != '') {
                    $filters = new stdClass();
                    $aFilters = explode(",", $params['filters']);
                    foreach($aFilters as $filter) {
                        $filter = trim($filter);
                        $filters->$filter = true;
                    }
                }
                return array('data' => $output->getObject($filters));
            } else {
                return array('errors' => $output->getErrorArray());
            }
        }

        public static function getHelp() {
            $path = driverCommand::run('modGetPath', array('name' => 'phpsysinfo'));
            $path = $path['path'].'drivers/phpsysinfo.ini';
            return array(
                "package" => 'phpsysinfo',
                "description" => __("Retrieve system information. If phpSysInfo get on error then return a array in 'errors'."), 
                "parameters" => array(
                        "plugin" => sprintf(__("Plugin to use. See '%s'"), $path),
                        "filters" => __("Comma separated list of sections to show or not. To show all sections set to '', empty (default). In case-sensitive each section name can be: Vitals, Network, Hardware, Memory, Filesystems, Mbinfo, Upsinfo.'"),
                    ), 
                "response" => array(
                        "errors" => __("List of errors."),
                    ),
                "type" => array(
                    "parameters" => array(
                        "plugin" => "string",
                        "filters" => "string",
                    ), 
                    "response" => array(
                        "errors" => "array",
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
            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
        }
    }
}
return new commandPSIInfo();
