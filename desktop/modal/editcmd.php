<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css', 'todo');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js', 'todo'); 

if (init('id') == '') {
    throw new Exception('{{L\'id de l\'opération ne peut etre vide : }}' . init('id'));
}
$idcmd = init('id');
$cmd= cmd::byId($idcmd);
$name = $cmd->getName();
$info = $cmd->getConfiguration('info');
$datetodo = $cmd->getConfiguration('cron_todo');
$timestamp = $cmd->getConfiguration('timestamp');


?>

<div class="input-group">
	<span class="input-group-addon" id="">Nom</span>
	<input type="text" class="form-control nameCmd" id="basic-url" aria-describedby="basic-addon3" value="<?php echo $name;?>">
</div>
<br/>
<div class="input-group">
	<span class="input-group-addon" id="">Date</span>
    <input type="text" id="datepicker" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="date" value="<?php echo $datetodo;?>" readonly >
    <input type="hidden" id="timestamp" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="timestamp" value="<?php echo $timestamp;?>"  >
</div>
<br/>
<div>
	<h4><span class="label label-info">Note</span></h2>
	<textarea class="form-control custom-control infocmd" rows="3" style="resize:none"><?php echo $info;?></textarea> 
</div>
<br/>
<form id ="form_cmdEdit" class="form-horizontal">
<button value="<?php echo $idcmd;?>" type="button" class="btn btn-success btn_editCmd">ok</button>
</form>

<script type="text/javascript">
	$(document).ready(function() {
		
		
		$( ".btn_editCmd" ).unbind().on('click', function() {
		
			var info = $('.infocmd').val(),
				nom  = $('.nameCmd').val(),
				id = $(this).val(),
				datetodo = $("#datepicker").val(),
				timestamp = $("#timestamp").val()

			editCmd(id,nom,info,datetodo,timestamp)			
		});
		
		function editCmd(_id,_nom, _info,_datetodo,_timestamp) {
			$.ajax({// fonction permettant de faire de l'ajax
				type: "POST", // methode de transmission des données au fichier php
				url: "plugins/todo/core/ajax/todo.ajax.php", // url du fichier php
				data: {
					action: "editCmd",
					id: _id,
					nom: _nom,
					info: _info,
					datetodo: _datetodo,
					timestamp: _timestamp
				},
				dataType: 'json',
				error: function(request, status, error) {
					handleAjaxError(request, status, error);
				},
				success: function(data) { // si l'appel a bien fonctionné
					if (data.state != 'ok') {
						$('#div_alert').showAlert({message:  data.result,level: 'danger'});
						return;
					}
				location.reload();
				}
			});			
		}
		
		$( "#datepicker" ).datetimepicker({lang: 'fr',
			format: 'd/m/Y',
			timepicker:false,
			minDate: 0,
			onClose: function(dateString) {
				console.log( dateString );
				var myDate = Math.floor(new Date(dateString).getTime()/1000) ;
				$('#timestamp').attr({value : myDate});
				
			}
		});	
	});
		
</script>








