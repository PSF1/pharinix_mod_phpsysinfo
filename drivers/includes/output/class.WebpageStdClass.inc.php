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
 * XML Generator class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   SVN: $Id: class.WebpageXML.inc.php 661 2012-08-27 11:26:39Z namiltd $
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * class for xml output
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class WebpageStdClass extends Output implements PSI_Interface_Output
{
    /**
     * xml object that holds the generated xml
     *
     * @var _stdClass
     */
    private $_xml;

    /**
     * only plugin xml
     *
     * @var boolean
     */
    private $_pluginRequest = false;

    /**
     * complete xml
     *
     * @var boolean
     */
    private $_completeXML = false;

    /**
     * name of the plugin
     *
     * @var string
     */
    private $_pluginName = null;

    /**
     * generate the output
     *
     * @return void
     */
    private function _prepare()
    {
        if (!$this->_pluginRequest) {
            // Figure out which OS we are running on, and detect support
            if (!file_exists(APP_ROOT.'/includes/os/class.'.PSI_OS.'.inc.php')) {
                $this->error->addError("file_exists(class.".PSI_OS.".inc.php)", PSI_OS." is not currently supported");
            }

            // check if there is a valid sensor configuration in phpsysinfo.ini
            $foundsp = array();
            if (defined('PSI_SENSOR_PROGRAM') && is_string(PSI_SENSOR_PROGRAM)) {
                if (preg_match(ARRAY_EXP, PSI_SENSOR_PROGRAM)) {
                    $sensorprograms = eval(strtolower(PSI_SENSOR_PROGRAM));
                } else {
                    $sensorprograms = array(strtolower(PSI_SENSOR_PROGRAM));
                }
                foreach ($sensorprograms as $sensorprogram) {
                    if (!file_exists(APP_ROOT.'/includes/mb/class.'.$sensorprogram.'.inc.php')) {
                        $this->error->addError("file_exists(class.".htmlspecialchars($sensorprogram).".inc.php)", "specified sensor program is not supported");
                    } else {
                        $foundsp[] = $sensorprogram;
                    }
                }
            }

            /**
             * motherboard information
             *
             * @var serialized array
             */
            define('PSI_MBINFO', serialize($foundsp));

            // check if there is a valid hddtemp configuration in phpsysinfo.ini
            $found = false;
            if (PSI_HDD_TEMP !== false) {
                $found = true;
            }
            /**
             * hddtemp information available or not
             *
             * @var boolean
             */
            define('PSI_HDDTEMP', $found);

            // check if there is a valid ups configuration in phpsysinfo.ini
            $foundup = array();
            if (defined('PSI_UPS_PROGRAM') && is_string(PSI_UPS_PROGRAM)) {
                if (preg_match(ARRAY_EXP, PSI_UPS_PROGRAM)) {
                    $upsprograms = eval(strtolower(PSI_UPS_PROGRAM));
                } else {
                    $upsprograms = array(strtolower(PSI_UPS_PROGRAM));
                }
                foreach ($upsprograms as $upsprogram) {
                    if (!file_exists(APP_ROOT.'/includes/ups/class.'.$upsprogram.'.inc.php')) {
                        $this->error->addError("file_exists(class.".htmlspecialchars($upsprogram).".inc.php)", "specified UPS program is not supported");
                    } else {
                        $foundup[] = $upsprogram;
                    }
                }
            }
            /**
             * ups information
             *
             * @var serialized array
             */
            define('PSI_UPSINFO', serialize($foundup));

            // if there are errors stop executing the script until they are fixed
            if ($this->error->errorsExist()) {
                return;
//                $this->error->errorsAsXML();
            }
        }

        // Create the XML
        if ($this->_pluginRequest) {
            $this->_xml = new _stdClass(false, $this->_pluginName);
        } else {
            $this->_xml = new _stdClass($this->_completeXML);
        }
    }

    /**
     * render the output
     *
     * @return void
     */
    public function run()
    {
//        header("Cache-Control: no-cache, must-revalidate\n");
//        header("Content-Type: text/xml\n\n");
//        $xml = $this->_xml->getXml();
//        echo $xml->asXML();
    }

    /**
     * get stdClass
     *
     * @param stdClass $filter Each property have true or false to include or not a section. In case-sensitive each section name can be: Vitals, Network, Hardware, Memory, Filesystems, Mbinfo, Upsinfo.
     * 
     * @return string
     */
    public function getObject($filter = null)
    {
        return $this->_xml->getXml($filter);
    }

    /**
     * set parameters for the XML generation process
     *
     * @param boolean $completeXML switch for complete xml with all plugins
     * @param string  $plugin      name of the plugin
     *
     * @return void
     */
    public function __construct($completeXML, $plugin = null)
    {
        parent::__construct();
        if (!$this->existError()) {
            if ($completeXML) {
                $this->_completeXML = true;
            }
            if ($plugin) {
                if (in_array(strtolower($plugin), CommonFunctions::getPlugins())) {
                    $this->_pluginName = $plugin;
                    $this->_pluginRequest = true;
                }
            }
            $this->_prepare();
        }
    }
    
    public function existError() {
        return $this->error->errorsExist();
    }
    
    public function getErrorArray() {
        return $this->error->errorsAsArray();
    }
}
