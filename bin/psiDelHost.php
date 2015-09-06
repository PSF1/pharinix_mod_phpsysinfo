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

if (!class_exists("commandPSIDelHost")) {
    class commandPSIDelHost extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                "url" => '',
            ), $params);
            
            if ($params['url'] == '') {
                return array('ok' => false, 'msg' => __('URL is required.'));
            }
            
            $test = driverCommand::run('getNodes', array(
                'nodetype' => 'psihost',
                'fields' => '`id`',
                'where' => "`url` = '{$params['url']}'",
            ));
            
            if (isset($test['ok']) && $test['ok'] === FALSE) {
                return $test;
            }
            
            $resp = array();
            foreach($test as $id => $node) {
                $resp[] = driverCommand::run('delNode', array(
                    'nodetype' => 'psihost',
                    'nid' => $id
                ));
            }
            
            return array('ok' => true, 'trace' => $resp);
        }

        public static function getHelp() {
            $path = driverCommand::run('modGetPath', array('name' => 'phpsysinfo'));
            $path = $path['path'];
            return array(
                "package" => 'phpsysinfo',
                "description" => __("Delete a Pharinix host to host's list."), 
                "parameters" => array(
                        "url" => __("Pharinix host's URL."),
                    ), 
                "response" => array(
                    'ok' => __('TRUE if ok.'),
                    'trace' => __('Delete node responses.'),
                ),
                "type" => array(
                    "parameters" => array(
                        "url" => 'string',
                    ), 
                    "response" => array(
                        'ok' => 'boolean',
                        'trace' => 'array',
                    ),
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
return new commandPSIDelHost();
