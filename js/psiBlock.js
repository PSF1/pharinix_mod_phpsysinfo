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

var cpuChart = null, cpuSerie = [];
var ramChart = null, ramSerie = [];
var swapChart = null, swapSerie = [];
var remotes = [];
$(document).ready(function(){
    var query = {
        command: 'psiInfo',
        interface: 'echoJson',
    };
    apiCall(query, function(e){ // onSuccess
        if (e.ok === false) {
            $('#psiBlock').html(e.msg);
        } else {
            var html = '';
            html += '<div class="row">';
            html += '<div class="col-md-6">';
                html += '<h1>';
                html += '<img src="'+PHARINIX_ROOT_URL+'?command=psiGetIcon&interface=nothing&file='+e.data.Vitals.Distroicon+'"/> ';
                html += e.data.Vitals.Hostname;
                html += '</h1>';
                html += '';
                html += e.data.Vitals.Distro+'<br><h6>'+e.data.Vitals.Kernel;
                html += '</h6>';
            html += '</div>';
            html += '<div class="col-md-6">';
                if (e.data.Hardware.Name) {
                    html += '<h2>'+e.data.Hardware.Name+'</h2>';
                }
                html += '<ul>';
                $.each(e.data.Hardware.CPU, function(i, item){
                    html += '<li>'+item.Model+'</li>';
                });
                html += '</ul>';
            html += '</div>';
            html += '</div>';
            
            html += '<div class="row">';
            html += '<div class="col-md-6"><h3>'+__('CPU load')+'</h3></div>';
            html += '<div class="col-md-6"><h3>'+__('Memory usage')+'</h3></div>';
            html += '</div>';
            html += '<div class="row">';
            html += '<div id="chart-cpu" class="col-md-6" style="height: 250px;"></div>';
            html += '<div class="col-md-4">'
            html += '<h5>'+__('RAM')+': '+formatBytes(e.data.Memory.Total)+'</h5>';
            html += '<div id="chart-ram" style="height: 200px;"></div>';
            html += '</div>';
            html += '<div class="col-md-2">'
            var swapTotal = 0;
            if (e.data.Memory.Swap) {
                swapTotal = e.data.Memory.Swap.Total;
            }
            html += '<h5>'+__('SWAP')+': '+formatBytes(swapTotal)+'</h5>';
            html += '<div id="chart-swap" style="height: 150px;"></div>';
            html += '</div>';
            html += '</div>';
            html += '<div id="chart-cpu-exist"></div>';
            $('#psiBlock').html(html);
            cpuSerie = {
                element: 'chart-cpu',
                data: [{
                    x: e.data.Generation.timestamp,
                    period: e.data.Generation.timestamp,
                    y: e.data.Vitals.LoadAvg,
                    load: e.data.Vitals.LoadAvg,
                }],
                xkey: 'period',
                ykeys: ['load'],
                labels: [__('CPU load')],
                pointSize: 2,
                hideHover: 'auto',
                resize: true
            };
            cpuChart = Morris.Area(cpuSerie);
            ramSerie = {
                element: 'chart-ram',
                data: [
                    {label: __("Free"), value: e.data.Memory.Free},
                    {label: __("Used"), value: e.data.Memory.Used},
                ],
                formatter: function (x, data) { 
                    return formatBytes(x); 
                },
            };
            ramChart = Morris.Donut(ramSerie);
            if (e.data.Memory.Swap) {
                swapSerie = {
                    element: 'chart-swap',
                    data: [
                        {label: __("Free"), value: e.data.Memory.Swap.Free},
                        {label: __("Used"), value: e.data.Memory.Swap.Used},
                    ],
                    formatter: function (x, data) { 
                        return formatBytes(x); 
                    },
                };
                swapChart = Morris.Donut(swapSerie);
            }
            var remoteList = '';
            remoteList += '<tr id="psihost_local">';
            var swap_used = 0, swap_total = 0;
            if (e.data.Memory.Swap) {
                swap_used = e.data.Memory.Swap.Used;
                swap_total = e.data.Memory.Swap.Total;
            }
            remoteList += getHostStatus('success', 'localhost', e.data.Vitals.LoadAvg, e.data.Memory.Used, e.data.Memory.Total, swap_used, swap_total);
            remoteList += '</tr>';
            $('#psiRemotes').html(remoteList);
            setTimeout(nextStep, 2000);
            // Load remote hosts
            var queryHosts = {
                command: 'getNodes',
                interface: 'echoJson',
                nodetype: 'psihost',
            };
            apiCall(queryHosts, function(e){
                remotes = e;
                $.each(remotes, function(id, host){
                    var remoteList = '';
                    remoteList += '<tr id="psihost_'+id+'">';
                    remoteList += getHostStatus('warning', host.url, 0, 0, 0, 0, 0);
                    remoteList += '</tr>';
                    $('#psiRemotes').append(remoteList);
                });
            });
        }
    },function(){ // onFail
        $('#psiBlock').html(__('Connection error.'));
    })
});

function getHostStatus(status, url, cpu, ram_used, ram_total, swap_used, swap_total) {
    var remoteList = '<td class="'+status+'">'; // Status
    remoteList += '</td>';
    remoteList += '<td>'; // Host
    if (url == 'localhost') {
        remoteList += url;
    } else {
        remoteList += '<a href="'+url+'" target="_blank">'+url+'</a>';
    }
    remoteList += '</td>';
    remoteList += '<td>'; // CPU
    remoteList += cpu;
    remoteList += '</td>';
    remoteList += '<td>'; // RAM
    remoteList += formatBytes(ram_used) + ' / ' + formatBytes(ram_total);
    remoteList += '</td>';
    remoteList += '<td>'; // SWAP
    remoteList += formatBytes(swap_used) + ' / ' + formatBytes(swap_total);
    remoteList += '</td>';
    return remoteList;
}

function nextStep() {
    if ($('#chart-cpu-exist')) {
        var query = {
            command: 'psiInfo',
            interface: 'echoJson',
            filters: 'Vitals,Memory',
        };
        apiCall(query, function(e){ // onSuccess
            cpuSerie.data.push({
                        x: e.data.Generation.timestamp,
                        period: e.data.Generation.timestamp,
                        y: e.data.Vitals.LoadAvg,
                        load: e.data.Vitals.LoadAvg,
                    });
            if (cpuSerie.data.length > 10) {
                cpuSerie.data.shift();
            }
            cpuChart.setData(cpuSerie.data);
            ramSerie = {
                element: 'chart-ram',
                data: [
                    {label: __("Free"), value: e.data.Memory.Free},
                    {label: __("Used"), value: e.data.Memory.Used},
                ],
            };
            ramChart.setData(ramSerie.data);
            if (e.data.Memory.Swap) {
                swapSerie = {
                    element: 'chart-swap',
                    data: [
                        {label: __("Free"), value: e.data.Memory.Swap.Free},
                        {label: __("Used"), value: e.data.Memory.Swap.Used},
                    ],
                };
                swapChart.setData(swapSerie.data);
            }
            setTimeout(nextStep, 2000);
            // Remotes
            var remoteList = '';
            var swap_used = 0, swap_total = 0;
            if (e.data.Memory.Swap) {
                swap_used = e.data.Memory.Swap.Used;
                swap_total = e.data.Memory.Swap.Total;
            }
            remoteList += getHostStatus('success', 'localhost', e.data.Vitals.LoadAvg, e.data.Memory.Used, e.data.Memory.Total, swap_used, swap_total);
            $('#psihost_local').html(remoteList);
            var queryHosts = {
                command: 'psiInfo',
                interface: 'echoJson',
                filters: 'Vitals,Memory',
            };
            $.each(remotes, function(id, host){
                remoteApiCall(host.url, queryHosts, function(e){
                    var remoteList = '';
                    var swap_used = 0, swap_total = 0;
                    if (e.data.Memory.Swap) {
                        swap_used = e.data.Memory.Swap.Used;
                        swap_total = e.data.Memory.Swap.Total;
                    }
                    remoteList += getHostStatus('success', host.url, e.data.Vitals.LoadAvg, e.data.Memory.Used, e.data.Memory.Total, swap_used, swap_total);
                    $('#psihost_'+id).html(remoteList);
                }, function(status, statusText, body){
                    var remoteList = '';
                    var errorMsg = '';
                    if (status != 0) {
                        errorMsg = __('Error')+' ' + status + '.';
                        errorMsg += statusText + '. ' + body;
                    } else {
                        errorMsg = __('Connection fail.');
                    }
                    remoteList += getHostStatus('danger', host.url, errorMsg, 0, 0, 0, 0);
                    $('#psihost_'+id).html(remoteList);
                });
            });
        });
    }
}