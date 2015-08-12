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
            html += '<h1>'+e.data.Vitals.Hostname+'</h1>';
            if (e.data.Hardware.Name) {
                html += '<h2>'+e.data.Hardware.Name+'</h2>';
            }
            html += '<ul>';
            $.each(e.data.Hardware.CPU, function(i, item){
                html += '<li>'+item.Model+'</li>';
            });
            html += '</ul>';
            html += '<h6>';
            html += '<img src="'+PHARINIX_ROOT_URL+'?command=psiGetIcon&interface=nothing&file='+e.data.Vitals.Distroicon+'" width="12px"/> ';
            html += e.data.Vitals.Distro+' - '+e.data.Vitals.Kernel;
            html += '</h6>';
            html += '<div class="row">';
            html += '<div class="col-md-6"><h3>CPU load</h3></div>';
            html += '<div class="col-md-6"><h3>Memory usage</h3></div>';
            html += '</div>';
            html += '<div class="row">';
            html += '<div id="chart-cpu" class="col-md-6" style="height: 250px;"></div>';
            html += '<div id="chart-ram" class="col-md-6" style="height: 250px;"></div>';
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
                labels: ['CPU load'],
                pointSize: 2,
                hideHover: 'auto',
                resize: true
            };
            cpuChart = Morris.Area(cpuSerie);
            ramSerie = {
                element: 'chart-ram',
                data: [
                    {label: "Free", value: e.data.Memory.Free},
                    {label: "Used", value: e.data.Memory.Used},
                ],
            };
            ramChart = Morris.Donut(ramSerie);
            setTimeout(nextStep, 2000);
        }
    },function(){ // onFail
        $('#psiBlock').html('Connection error.');
    })
});

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
                    {label: "Free", value: e.data.Memory.Free},
                    {label: "Used", value: e.data.Memory.Used},
                ],
            };
            ramChart.setData(ramSerie.data);
            setTimeout(nextStep, 2000);
        });
    }
}