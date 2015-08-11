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
 * stdClass Generation class
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Pedro Pelaez <aaaaa976@gmail.com>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   
 * @link      http://phpsysinfo.sourceforge.net
 */
 /**
 * class for generation of the xml
 *
 * @category  PHP
 * @package   PSI_XML
 * @author    Michael Cramer <BigMichi1@users.sourceforge.net>
 * @copyright 2009 phpSysInfo
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @version   Release: 3.0
 * @link      http://phpsysinfo.sourceforge.net
 */
class _stdClass
{
    /**
     * Sysinfo object where the information retrieval methods are included
     *
     * @var PSI_Interface_OS
     */
    private $_sysinfo;

    /**
     * @var System
     */
    private $_sys = null;

    /**
     * xml object with the xml content
     *
     * @var SimpleXMLExtended
     */
    private $_xml;

    /**
     * object for error handling
     *
     * @var Error
     */
    private $_errors;

    /**
     * array with all enabled plugins (name)
     *
     * @var array
     */
    private $_plugins;

    /**
     * plugin name if pluginrequest
     *
     * @var string
     */
    private $_plugin = '';

    /**
     * generate a xml for a plugin or for the main app
     *
     * @var boolean
     */
    private $_plugin_request = false;

    /**
     * generate the entire xml with all plugins or only a part of the xml (main or plugin)
     *
     * @var boolean
     */
    private $_complete_request = false;

    /**
     * doing some initial tasks
     * - generate the xml structure with the right header elements
     * - get the error object for error output
     * - get a instance of the sysinfo object
     *
     * @param boolean $complete   generate xml with all plugins or not
     * @param string  $pluginname name of the plugin
     *
     * @return void
     */
    public function __construct($complete = false, $pluginname = "")
    {
        $this->_errors = Error::singleton();
        if ($pluginname == "") {
            $this->_plugin_request = false;
            $this->_plugin = '';
        } else {
            $this->_plugin_request = true;
            $this->_plugin = $pluginname;
        }
        if ($complete) {
            $this->_complete_request = true;
        } else {
            $this->_complete_request = false;
        }
        $os = PSI_OS;
        $this->_sysinfo = new $os();
        $this->_plugins = CommonFunctions::getPlugins();
        $this->_buildBody();
    }

    /**
     * generate common information
     *
     * @return void
     */
    private function _buildVitals()
    {
        $this->_xml->Vitals = new stdClass();
        $this->_xml->Vitals->Hostname = $this->_sys->getHostname();
        $this->_xml->Vitals->IPAddr = $this->_sys->getIp();
        $this->_xml->Vitals->Kernel = $this->_sys->getKernel();
        $this->_xml->Vitals->Distro = $this->_sys->getDistribution();
        $this->_xml->Vitals->Distroicon = $this->_sys->getDistributionIcon();
        $this->_xml->Vitals->Uptime = $this->_sys->getUptime();
        $this->_xml->Vitals->Users = $this->_sys->getUsers();
        $this->_xml->Vitals->LoadAvg = $this->_sys->getLoad();
        if ($this->_sys->getLoadPercent() !== null) {
            $this->_xml->Vitals->CPULoad = $this->_sys->getLoadPercent();
        }
        if ($this->_sysinfo->getLanguage() !== null) {
            $this->_xml->Vitals->SysLang = $this->_sysinfo->getLanguage();
        }
        if ($this->_sysinfo->getEncoding() !== null) {
            $this->_xml->Vitals->CodePage = $this->_sysinfo->getEncoding();
        }

        //processes
        if (($procss = $this->_sys->getProcesses()) !== null) {
            if (isset($procss['*']) && (($procall = $procss['*']) > 0)) {
                $this->_xml->Vitals->Processes = $procall;
                if (!isset($procss[' ']) || !($procss[' '] > 0)) { // not unknown
                    $procsum = 0;
                    if (isset($procss['R']) && (($proctmp = $procss['R']) > 0)) {
                        $this->_xml->Vitals->ProcessesRunning = $proctmp;
                        $procsum += $proctmp;
                    }
                    if (isset($procss['S']) && (($proctmp = $procss['S']) > 0)) {
                        $this->_xml->Vitals->ProcessesSleeping = $proctmp;
                        $procsum += $proctmp;
                    }
                    if (isset($procss['T']) && (($proctmp = $procss['T']) > 0)) {
                        $this->_xml->Vitals->ProcessesStopped = $proctmp;
                        $procsum += $proctmp;
                    }
                    if (isset($procss['Z']) && (($proctmp = $procss['Z']) > 0)) {
                        $this->_xml->Vitals->ProcessesZombie = $proctmp;
                        $procsum += $proctmp;
                    }
                    if (isset($procss['D']) && (($proctmp = $procss['D']) > 0)) {
                        $this->_xml->Vitals->ProcessesWaiting = $proctmp;
                        $procsum += $proctmp;
                    }
                    if (($proctmp = $procall - $procsum) > 0) {
                        $this->_xml->Vitals->ProcessesOther = $proctmp;
                    }
                }
            }
        }
        $this->_xml->Vitals->OS = PSI_OS;
    }

    /**
     * generate the network information
     *
     * @return void
     */
    private function _buildNetwork()
    {
        $hideDevices = array();
        $this->_xml->Network = array();
        if (defined('PSI_HIDE_NETWORK_INTERFACE')) {
            if (is_string(PSI_HIDE_NETWORK_INTERFACE)) {
                if (preg_match(ARRAY_EXP, PSI_HIDE_NETWORK_INTERFACE)) {
                    $hideDevices = eval(PSI_HIDE_NETWORK_INTERFACE);
                } else {
                    $hideDevices = array(PSI_HIDE_NETWORK_INTERFACE);
                }
            } elseif (PSI_HIDE_NETWORK_INTERFACE === true) {
                return;
            }
        }
        foreach ($this->_sys->getNetDevices() as $dev) {
            if (!in_array(trim($dev->getName()), $hideDevices)) {
                $device = new stdClass();
                $device->Name = utf8_encode($dev->getName());
                $device->RxBytes = $dev->getRxBytes();
                $device->TxBytes = $dev->getTxBytes();
                $device->Err = $dev->getErrors();
                $device->Drops = $dev->getDrops();
                if (defined('PSI_SHOW_NETWORK_INFOS') && PSI_SHOW_NETWORK_INFOS && $dev->getInfo())
                    $device->Info = $dev->getInfo();
                $this->_xml->Network[] = $device;
            }
        }
    }

    /**
     * generate the hardware information
     *
     * @return void
     */
    private function _buildHardware()
    {
        $dev = new HWDevice();
        $this->_xml->Hardware = new stdClass();
        $hardware = &$this->_xml->Hardware;
        if ($this->_sys->getMachine() != "") {
            $hardware->Name = utf8_encode($this->_sys->getMachine());
        }
        $pci = null;
        foreach (System::removeDupsAndCount($this->_sys->getPciDevices()) as $dev) {
            if ($pci === null) {
                $hardware->PCI = array();
                $pci = &$hardware->PCI;
            }
            $tmp = new stdClass();
            $tmp->Name = utf8_encode($dev->getName());
            $tmp->Count = $dev->getCount();
            $pci[] = $tmp;
        }
        $usb = null;
        foreach (System::removeDupsAndCount($this->_sys->getUsbDevices()) as $dev) {
            if ($usb === null) {
                $hardware->USB = array();
                $usb = &$hardware->USB;
            }
            $tmp = new stdClass();
            $tmp->Name = utf8_encode($dev->getName());
            $tmp->Count = $dev->getCount();
            $usb[] = $tmp;
        }
        $ide = null;
        foreach (System::removeDupsAndCount($this->_sys->getIdeDevices()) as $dev) {
            if ($ide === null) {
                $hardware->IDE = array();
                $ide = &$hardware->IDE;
            }
            $tmp = new stdClass();
            $tmp->Name = utf8_encode($dev->getName());
            $tmp->Count = $dev->getCount();
            if ($dev->getCapacity() !== null) {
                $tmp->Capacity = $dev->getCapacity();
            }
            $ide[] = $tmp;
        }
        $scsi = null;
        foreach (System::removeDupsAndCount($this->_sys->getScsiDevices()) as $dev) {
            if ($scsi === null) {
                $hardware->SCSI = array();
                $scsi = &$hardware->SCSI;
            }
            $tmp = new stdClass();
            $tmp->Name = utf8_encode($dev->getName());
            $tmp->Count = $dev->getCount();
            if ($dev->getCapacity() !== null) {
                $tmp->Capacity = $dev->getCapacity();
            }
            $scsi[] = $tmp;
        }
        $tb = null;
        foreach (System::removeDupsAndCount($this->_sys->getTbDevices()) as $dev) {
            if ($tb === null) {
                $hardware->TB = array();
                $tb = &$hardware->TB;
            }
            $tmp = new stdClass();
            $tmp->Name = utf8_encode($dev->getName());
            $tmp->Count = $dev->getCount();
            $tb[] = $tmp;
        }

        $cpu = null;
        foreach ($this->_sys->getCpus() as $oneCpu) {
            if ($cpu === null) {
                $hardware->CPU = array();
                $cpu = &$hardware->CPU;
            }
            $tmp = new stdClass();
            $tmp->Model = $oneCpu->getModel();
            if ($oneCpu->getCpuSpeed() !== 0) {
                $tmp->CpuSpeed = $oneCpu->getCpuSpeed();
            }
            if ($oneCpu->getCpuSpeedMax() !== 0) {
                $tmp->CpuSpeedMax = $oneCpu->getCpuSpeedMax();
            }
            if ($oneCpu->getCpuSpeedMin() !== 0) {
                $tmp->CpuSpeedMin = $oneCpu->getCpuSpeedMin();
            }
            if ($oneCpu->getTemp() !== null) {
                $tmp->CpuTemp = $oneCpu->getTemp();
            }
            if ($oneCpu->getBusSpeed() !== null) {
                $tmp->BusSpeed = $oneCpu->getBusSpeed();
            }
            if ($oneCpu->getCache() !== null) {
                $tmp->Cache = $oneCpu->getCache();
            }
            if ($oneCpu->getVirt() !== null) {
                $tmp->Virt = $oneCpu->getVirt();
            }
            if ($oneCpu->getBogomips() !== null) {
                $tmp->Bogomips = $oneCpu->getBogomips();
            }
            if ($oneCpu->getLoad() !== null) {
                $tmp->Load = $oneCpu->getLoad();
            }
            $cpu[] = $tmp;
        }
    }

    /**
     * generate the memory information
     *
     * @return void
     */
    private function _buildMemory()
    {
        $this->_xml->Memory = new stdClass();
        $this->_xml->Memory->Free = $this->_sys->getMemFree();
        $this->_xml->Memory->Used = $this->_sys->getMemUsed();
        $this->_xml->Memory->Total = $this->_sys->getMemTotal();
        $this->_xml->Memory->Percent = $this->_sys->getMemPercentUsed();
        if (($this->_sys->getMemApplication() !== null) || ($this->_sys->getMemBuffer() !== null) || ($this->_sys->getMemCache() !== null)) {
            $this->_xml->Memory->Details = new stdClass();
            if ($this->_sys->getMemApplication() !== null) {
                $this->_xml->Memory->Details->App = $this->_sys->getMemApplication();
                $this->_xml->Memory->Details->AppPercent = $this->_sys->getMemPercentApplication();
            }
            if ($this->_sys->getMemBuffer() !== null) {
                $this->_xml->Memory->Details->Buffers = $this->_sys->getMemBuffer();
                $this->_xml->Memory->Details->BuffersPercent = $this->_sys->getMemPercentBuffer();
            }
            if ($this->_sys->getMemCache() !== null) {
                $this->_xml->Memory->Details->Cached = $this->_sys->getMemCache();
                $this->_xml->Memory->Details->CachedPercent = $this->_sys->getMemPercentCache();
            }
        }
        if (count($this->_sys->getSwapDevices()) > 0) {
            $this->_xml->Memory->Swap = new stdClass();
            $this->_xml->Memory->Swap->Free = $this->_sys->getSwapFree();
            $this->_xml->Memory->Swap->Used = $this->_sys->getSwapUsed();
            $this->_xml->Memory->Swap->Total = $this->_sys->getSwapTotal();
            $this->_xml->Memory->Swap->Percent = $this->_sys->getSwapPercentUsed();
            $i = 1;
            $this->_xml->Memory->Swap->Mount = array();
            foreach ($this->_sys->getSwapDevices() as $dev) {
                $swapMount = new stdClass();
                $this->_fillDevice($swapMount, $dev, $i++);
                $this->_xml->Memory->Swap->Mount[] = $swapMount;
            }
        }
    }

    /**
     * fill a stdClass element with atrributes from a disk device
     *
     * @param stdClass $mount Object Element
     * @param DiskDevice        $dev   DiskDevice
     * @param Integer           $i     counter
     *
     * @return Void
     */
    private function _fillDevice(stdClass &$mount, DiskDevice $dev, $i)
    {
        $mount->MountPointID = $i;
        $mount->FSType = $dev->getFsType();
        $mount->Name = utf8_encode($dev->getName());
        $mount->Free = sprintf("%.0f", $dev->getFree());
        $mount->Used = sprintf("%.0f", $dev->getUsed());
        $mount->Total = sprintf("%.0f", $dev->getTotal());
        $mount->Percent = $dev->getPercentUsed();
        if (PSI_SHOW_MOUNT_OPTION === true) {
            if ($dev->getOptions() !== null) {
                $mount->MountOptions = preg_replace("/,/", ", ", $dev->getOptions());
            }
        }
        if ($dev->getPercentInodesUsed() !== null) {
            $mount->Inodes = $dev->getPercentInodesUsed();
        }
        if (PSI_SHOW_MOUNT_POINT === true) {
            $mount->MountPoint = $dev->getMountPoint();
        }
    }

    /**
     * generate the filesysteminformation
     *
     * @return void
     */
    private function _buildFilesystems()
    {
        $hideMounts = $hideFstypes = $hideDisks = array();
        $i = 1;
        if (defined('PSI_HIDE_MOUNTS') && is_string(PSI_HIDE_MOUNTS)) {
            if (preg_match(ARRAY_EXP, PSI_HIDE_MOUNTS)) {
                $hideMounts = eval(PSI_HIDE_MOUNTS);
            } else {
                $hideMounts = array(PSI_HIDE_MOUNTS);
            }
        }
        if (defined('PSI_HIDE_FS_TYPES') && is_string(PSI_HIDE_FS_TYPES)) {
            if (preg_match(ARRAY_EXP, PSI_HIDE_FS_TYPES)) {
                $hideFstypes = eval(PSI_HIDE_FS_TYPES);
            } else {
                $hideFstypes = array(PSI_HIDE_FS_TYPES);
            }
        }
        if (defined('PSI_HIDE_DISKS')) {
            if (is_string(PSI_HIDE_DISKS)) {
                if (preg_match(ARRAY_EXP, PSI_HIDE_DISKS)) {
                    $hideDisks = eval(PSI_HIDE_DISKS);
                } else {
                    $hideDisks = array(PSI_HIDE_DISKS);
                }
            } elseif (PSI_HIDE_DISKS === true) {
                return;
            }
        }
        $this->_xml->FileSystem = array();
        foreach ($this->_sys->getDiskDevices() as $disk) {
            if (!in_array($disk->getMountPoint(), $hideMounts, true) && !in_array($disk->getFsType(), $hideFstypes, true) && !in_array($disk->getName(), $hideDisks, true)) {
                $mount = new stdClass();
                $this->_fillDevice($mount, $disk, $i++);
                $this->_xml->FileSystem[] = $mount;
            }
        }
    }

    /**
     * generate the motherboard information
     *
     * @return void
     */
    private function _buildMbinfo()
    {
        $this->_xml->MBInfo = new stdClass();
        $temp = $fan = $volt = $power = $current = null;

        if (sizeof(unserialize(PSI_MBINFO))>0) {
            foreach (unserialize(PSI_MBINFO) as $mbinfoclass) {
                $mbinfo_data = new $mbinfoclass();
                $mbinfo_detail = $mbinfo_data->getMBInfo();

                foreach ($mbinfo_detail->getMbTemp() as $dev) {
                    if ($temp == null) {
                        $this->_xml->MBInfo->Temperature = array();
                        $temp = &$this->_xml->MBInfo->Temperature;
                    }
                    $item = new stdClass();
                    $item->Label = $dev->getName();
                    $item->Value = $dev->getValue();
                    if ($dev->getMax() !== null) {
                        $item->Max = $dev->getMax();
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->Event = $dev->getEvent();
                    }
                    $temp[] = $item;
                }

                foreach ($mbinfo_detail->getMbFan() as $dev) {
                    if ($fan == null) {
                        $this->_xml->MBInfo->Fans = array();
                        $fan = &$this->_xml->MBInfo->Fans;
                    }
                    $item = new stdClass();
                    $item->Label = $dev->getName();
                    $item->Value = $dev->getValue();
                    if ($dev->getMin() !== null) {
                        $item->Min = $dev->getMin();
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->Event = $dev->getEvent();
                    }
                    $fan[] = $item;
                }

                foreach ($mbinfo_detail->getMbVolt() as $dev) {
                    if ($volt == null) {
                        $this->_xml->MBInfo->Voltage = array();
                        $volt = &$this->_xml->MBInfo->Voltage;
                    }
                    $item = new stdClass();
                    $item->Label = $dev->getName();
                    $item->Value = $dev->getValue();
                    if ($dev->getMin() !== null) {
                        $item->Min = $dev->getMin();
                    }
                    if ($dev->getMax() !== null) {
                        $item->Max = $dev->getMax();
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->Event = $dev->getEvent();
                    }
                    $volt[] = $item;
                }

                foreach ($mbinfo_detail->getMbPower() as $dev) {
                    if ($power == null) {
                        $this->_xml->MBInfo->Power = array();
                        $power = &$this->_xml->MBInfo->Power;
                    }
                    $item = new stdClass();
                    $item->Label = $dev->getName();
                    $item->Value = $dev->getValue();
                    if ($dev->getMax() !== null) {
                        $item->Max = $dev->getMax();
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->Event = $dev->getEvent();
                    }
                    $power[] = $item;
                }

                foreach ($mbinfo_detail->getMbCurrent() as $dev) {
                    if ($current == null) {
                        $this->_xml->MBInfo->Current = array();
                        $current = &$this->_xml->MBInfo->Current;
                    }
                    $item = new stdClass();
                    $item->Label = $dev->getName();
                    $item->Value = $dev->getValue();
                    if ($dev->getMax() !== null) {
                        $item->Max = $dev->getMax();
                    }
                    if (defined('PSI_SENSOR_EVENTS') && PSI_SENSOR_EVENTS && $dev->getEvent() !== "") {
                        $item->Event = $dev->getEvent();
                    }
                    $current[] = $item;
                }
            }
        }

        if (PSI_HDDTEMP) {
            $hddtemp = new HDDTemp();
            $hddtemp_data = $hddtemp->getMBInfo();
            foreach ($hddtemp_data->getMbTemp() as $dev) {
                if ($temp == null) {
                    $this->_xml->MBInfo->Temperature = array();
                    $temp = &$this->_xml->MBInfo->Temperature;
                }
                $item = new stdClass();
                $item->Label = $dev->getName();
                $item->Value = $dev->getValue();
                if ($dev->getMax() !== null) {
                    $item->Max = $dev->getMax();
                }
                $temp[] = $item;
            }
        }
    }

    /**
     * generate the ups information
     *
     * @return void
     */
    private function _buildUpsinfo()
    {
        $this->_xml->UPSInfo = new stdClass();
        if (defined('PSI_UPS_APCUPSD_CGI_ENABLE') && PSI_UPS_APCUPSD_CGI_ENABLE) {
            $this->_xml->UPSInfo->ApcupsdCgiLinks = true;
        }
        if (sizeof(unserialize(PSI_UPSINFO))>0) {
            foreach (unserialize(PSI_UPSINFO) as $upsinfoclass) {
                $upsinfo_data = new $upsinfoclass();
                $upsinfo_detail = $upsinfo_data->getUPSInfo();
                $this->_xml->UPSInfo->UPS = array();
                foreach ($upsinfo_detail->getUpsDevices() as $ups) {
                    $item = new stdClass();
                    $item->Name = utf8_encode($ups->getName());
                    if ($ups->getModel() !== "") {
                        $item->Model = $ups->getModel();
                    }
                    $item->Mode = $ups->getMode();
                    if ($ups->getStartTime() !== "") {
                        $item->StartTime = $ups->getStartTime();
                    }
                    $item->Status = $ups->getStatus();
                    if ($ups->getTemperatur() !== null) {
                        $item->Temperature = $ups->getTemperatur();
                    }
                    if ($ups->getOutages() !== null) {
                        $item->OutagesCount = $ups->getOutages();
                    }
                    if ($ups->getLastOutage() !== null) {
                        $item->LastOutage = $ups->getLastOutage();
                    }
                    if ($ups->getLastOutageFinish() !== null) {
                        $item->LastOutageFinish = $ups->getLastOutageFinish();
                    }
                    if ($ups->getLineVoltage() !== null) {
                        $item->LineVoltage = $ups->getLineVoltage();
                    }
                    if ($ups->getLineFrequency() !== null) {
                        $item->LineFrequency = $ups->getLineFrequency();
                    }
                    if ($ups->getLoad() !== null) {
                        $item->LoadPercent = $ups->getLoad();
                    }
                    if ($ups->getBatteryDate() !== null) {
                        $item->BatteryDate = $ups->getBatteryDate();
                    }
                    if ($ups->getBatteryVoltage() !== null) {
                        $item->BatteryVoltage = $ups->getBatteryVoltage();
                    }
                    if ($ups->getBatterCharge() !== null) {
                        $item->BatteryChargePercent = $ups->getBatterCharge();
                    }
                    if ($ups->getTimeLeft() !== null) {
                        $item->TimeLeftMinutes = $ups->getTimeLeft();
                    }
                    $this->_xml->UPSInfo->UPS[] = $item;
                }
            }
        }
    }

    /**
     * generate the stdClass document
     * 
     * @param stdClass $filter Each property have true or false to include or not a section. In case-sensitive each section name can be: Vitals, Network, Hardware, Memory, Filesystems, Mbinfo, Upsinfo.
     * @return void
     */
    private function _buildStdClass($filter = null)
    {
        if ($filter == null) {
            $filter = new stdClass();
            $filter->Vitals = true;
            $filter->Network = true;
            $filter->Hardware = true;
            $filter->Memory = true;
            $filter->Filesystems = true;
            $filter->Mbinfo = true;
            $filter->Upsinfo = true;
        } else {
            if (!isset($filter->Vitals)) $filter->Vitals = false;
            if (!isset($filter->Network)) $filter->Network = false;
            if (!isset($filter->Hardware)) $filter->Hardware = false;
            if (!isset($filter->Memory)) $filter->Memory = false;
            if (!isset($filter->Filesystems)) $filter->Filesystems = false;
            if (!isset($filter->Mbinfo)) $filter->Mbinfo = false;
            if (!isset($filter->Upsinfo)) $filter->Upsinfo = false;
        }
        if (!$this->_plugin_request || $this->_complete_request) {
            if ($this->_sys === null) {
                if (PSI_DEBUG === true) {
                    // Safe mode check
                    $safe_mode = @ini_get("safe_mode") ? true : false;
                    if ($safe_mode) {
                        $this->_errors->addError("WARN", "PhpSysInfo requires to set off 'safe_mode' in 'php.ini'");
                    }
                    // Include path check
                    $include_path = @ini_get("include_path");
                    if ($include_path && ($include_path!="")) {
                        $include_path = preg_replace("/(:)|(;)/", "\n", $include_path);
                        if (preg_match("/^\.$/m", $include_path)) {
                            $include_path = ".";
                        }
                    }
                    if ($include_path != ".") {
                        $this->_errors->addError("WARN", "PhpSysInfo requires '.' inside the 'include_path' in php.ini");
                    }
                    // popen mode check
                    if (defined("PSI_MODE_POPEN") && PSI_MODE_POPEN === true) {
                        $this->_errors->addError("WARN", "Installed version of PHP does not support proc_open() function, popen() is used");
                    }
                }
                $this->_sys = $this->_sysinfo->getSys();
            }
            if (!isset($filter->Vitals) || $filter->Vitals ) $this->_buildVitals();
            if (!isset($filter->Network) || $filter->Network ) $this->_buildNetwork();
            if (!isset($filter->Hardware) || $filter->Hardware ) $this->_buildHardware();
            if (!isset($filter->Memory) || $filter->Memory ) $this->_buildMemory();
            if (!isset($filter->Filesystems) || $filter->Filesystems ) $this->_buildFilesystems();
            if (!isset($filter->Mbinfo) || $filter->Mbinfo ) $this->_buildMbinfo();
            if (!isset($filter->Upsinfo) || $filter->Upsinfo ) $this->_buildUpsinfo();
        }
        $this->_buildPlugins();
        $this->_xml->errors = $this->_errors->errorsAsArray();
    }

    /**
     * get the stdClass object
     * @param stdClass $filter Each property have true or false to include or not a section. In case-sensitive each section name can be: Vitals, Network, Hardware, Memory, Filesystems, Mbinfo, Upsinfo.
     * 
     * @return string
     */
    public function getXml($filter = null)
    {
        $this->_buildStdClass($filter);
        
        return $this->_xml;
    }

    /**
     * include xml-trees of the plugins to the main xml
     *
     * @return void
     */
    private function _buildPlugins()
    {
//        $pluginroot = $this->_xml->addChild("Plugins");
//        if (($this->_plugin_request || $this->_complete_request) && count($this->_plugins) > 0) {
//            $plugins = array();
//            if ($this->_complete_request) {
//                $plugins = $this->_plugins;
//            }
//            if ($this->_plugin_request) {
//                $plugins = array($this->_plugin);
//            }
//            foreach ($plugins as $plugin) {
//                $object = new $plugin($this->_sysinfo->getEncoding());
//                $object->execute();
//                $oxml = $object->xml();
//                if (sizeof($oxml) > 0) {
//                    $pluginroot->combinexml($oxml);
//                }
//            }
//        }
    }

    /**
     * build the stdClass structure where the content can be inserted
     *
     * @return void
     */
    private function _buildBody()
    {
//        $dom = new DOMDocument('1.0', 'UTF-8');
//        $root = $dom->createElement("tns:phpsysinfo");
//        $root->setAttribute('xmlns:tns', 'http://phpsysinfo.sourceforge.net/');
//        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
//        $root->setAttribute('xsi:schemaLocation', 'http://phpsysinfo.sourceforge.net/ phpsysinfo3.xsd');
//        $dom->appendChild($root);
//        $this->_xml = new SimpleXMLExtended(simplexml_import_dom($dom), $this->_sysinfo->getEncoding());
        $this->_xml = new stdClass();

        $this->_xml->Generation = new stdClass();
        $this->_xml->Generation->version = PSI_VERSION_STRING;
        $this->_xml->Generation->timestamp = time();
        $this->_xml->Options = new stdClass();
        $this->_xml->Options->tempFormat = defined('PSI_TEMP_FORMAT') ? strtolower(PSI_TEMP_FORMAT) : 'c';
        $this->_xml->Options->byteFormat = defined('PSI_BYTE_FORMAT') ? strtolower(PSI_BYTE_FORMAT) : 'auto_binary';
        if (defined('PSI_REFRESH')) {
            if (PSI_REFRESH === false) {
                $this->_xml->Options->refresh = 0;
            } elseif (PSI_REFRESH === true) {
                $this->_xml->Options->refresh = 1;
            } else {
                $this->_xml->Options->refresh = PSI_REFRESH;
            }
        } else {
            $this->_xml->Options->refresh = 60000;
        }
        if (defined('PSI_FS_USAGE_THRESHOLD')) {
            if (PSI_FS_USAGE_THRESHOLD === true) {
                $this->_xml->Options->threshold = 1;
            } elseif ((PSI_FS_USAGE_THRESHOLD !== false) && (PSI_FS_USAGE_THRESHOLD >= 1) && (PSI_FS_USAGE_THRESHOLD <= 99)) {
                $this->_xml->Options->threshold = PSI_FS_USAGE_THRESHOLD;
            }
        } else {
            $this->_xml->Options->threshold = 90;
        }
        $this->_xml->Options->showPickListTemplate = defined('PSI_SHOW_PICKLIST_TEMPLATE') ? (PSI_SHOW_PICKLIST_TEMPLATE ? 'true' : 'false') : 'false';
        $this->_xml->Options->showPickListLang = defined('PSI_SHOW_PICKLIST_LANG') ? (PSI_SHOW_PICKLIST_LANG ? 'true' : 'false') : 'false';
        $this->_xml->Options->showCPUListExpanded = defined('PSI_SHOW_CPULIST_EXPANDED') ? (PSI_SHOW_CPULIST_EXPANDED ? 'true' : 'false') : 'true';
        $this->_xml->Options->showCPUInfoExpanded = defined('PSI_SHOW_CPUINFO_EXPANDED') ? (PSI_SHOW_CPUINFO_EXPANDED ? 'true' : 'false') : 'false';
        if (count($this->_plugins) > 0) {
            if ($this->_plugin_request) {
                $this->_xml->UsedPlugins = new stdClass();
                $this->_xml->UsedPlugins->Plugin = array();
                $pItem = new stdClass();
                $pItem->name = utf8_encode($this->_plugin);
                $this->_xml->UsedPlugins->Plugin[] = $pItem;
            } elseif ($this->_complete_request) {
                $this->_xml->UsedPlugins = new stdClass();
                $this->_xml->UsedPlugins->Plugin = array();
                foreach ($this->_plugins as $plugin) {
                    $pItem = new stdClass();
                    $pItem->name = utf8_encode($plugin);
                    $this->_xml->UsedPlugins->Plugin[] = $pItem;
                }
            } else {
                $this->_xml->UnusedPlugins = new stdClass();
                $this->_xml->UnusedPlugins->Plugin = array();
                foreach ($this->_plugins as $plugin) {
                    $pItem = new stdClass();
                    $pItem->name = utf8_encode($plugin);
                    $this->_xml->UnusedPlugins->Plugin[] = $pItem;
                }
            }
        }
    }
}
