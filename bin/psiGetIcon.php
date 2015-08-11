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

if (!class_exists("commandPSIGetIcon")) {
    class commandPSIGetIcon extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $params = array_merge(array(
                'file' => '',
            ), $params);
            
            $path = driverCommand::run('modGetPath', array(
                'name' => 'phpsysinfo',
            ));
            $path = $path['path'];
            $params["file"] = str_replace('..', '', $params["file"]);
            $filename = $path.'drivers/gfx/images/'.$params["file"];
            if (!is_file($filename)) { 
                return array('ok' => false, 'msg' => 'System icon not found.');
            }

            header("Content-type: image/png");
            header("Content-Disposition:inline;filename=\"".$params['file']."\"");
            header('Content-Length: ' . filesize($filename));
            //header("Cache-control: private"); //use this to open files directly                     
            readfile($filename);
        }

        public static function getHelp() {
            $path = driverCommand::run('modGetPath', array('name' => 'phpsysinfo'));
            $path = $path['path'];
            return array(
                "description" => "Download a system icon.", 
                "parameters" => array(
                        "file" => "PNG system icon to download.",
                    ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        "file" => "string",
                    ), 
                    "response" => array(),
                )
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
return new commandPSIGetIcon();
