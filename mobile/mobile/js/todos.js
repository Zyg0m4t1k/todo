
function initTodoTodos (_object_id) {
	$.ajax({
		type: 'POST',
		url: 'plugins/todo/core/ajax/todo.ajax.php',
		data: {
			action: 'getAllTodo',
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			}
			$('#div_displayEquipementTodo').empty();
			var html = '';
		
			for(var i in data.result){
				  html += '<div class="list_mobile"><center><h4>' + data.result[i].nom + '</h4></center><div class="input-group"><span class="input-group-btn"><button class=" addTodo btn btn-default" type="button" value ="' + data.result[i].id +'" >Add!</button></span><input id="input_' + data.result[i].id +'" type="text" class="form-control" placeholder="Todo" ></div>';      
				  html += '<ul class="list-group" style="margin:10px;">';
				  for(var j in data.result[i].nom_cmd){
					  if ( data.result[i].timestamp[j] != '') {
						   var now = Math.round(new Date().getTime() / 1000);
						   if ( now > data.result[i].timestamp[j] && now < (parseInt(data.result[i].timestamp[j]) + parseInt(86400)) ) {
							   var class_todo = 'today';
						   } else if ( (parseInt(data.result[i].timestamp[j]) + parseInt(86400)) > now ) {
							   var class_todo = 'green';
						   } else  {
							   var class_todo = 'red';
						   }
					  } else {
						  var class_todo = '';
					  }
					   if (data.result[i].active[j] == 0) {
							decoration = 'line-through';
							checked = 'checked'
						} else {
							decoration = 'none';
							checked = 'unchecked'
						}
				  html += '<li id="' + data.result[i].id_cmd[j] + '" class="list-group-item" style="text-decoration:' + decoration + ';background-color:transparent; font-size : 0.9em;"><span><input type="checkbox" value="' + data.result[i].id_cmd[j] + '" ' + checked + '></span><span class="name_event">' + data.result[i].nom_cmd[j] + '</span><div class="actions"><a href="" name="' + data.result[i].id + '" h class="' + class_todo + '" alt="' + data.result[i].id_cmd[j] + '">edit</a><a href="" name="' + data.result[i].id + '" h class="delete" alt="' + data.result[i].id_cmd[j] + '">delete</a></div></li>' ;
				  }
				  html += '</ul>';
				  html += '</div>';
			}
			$('#div_displayEquipementTodo').html(html);
			
			
			$('.list-group :checkbox').change(function() {
				id = $(this).val();
				li = $('#'+id).text();
				if(this.checked) {
					$('#'+id).css("text-decoration", "line-through");
					changeTodo('check', id, 0)
				} else {
					$('#'+id).css("text-decoration", "none");
					changeTodo('check', id, 1)
				}
				
			});
		
			
				
			$( ".addTodo" ).unbind().on('click', function() {
				id = $(this).val();
				input = $('#input_'+id).val();
				if (input == '') {
					return
				}
				//$("ul").append('<li id="'+ id + '" class="list-group-item" style="background-color:transparent;font-size : 0.9em;">' + input + '</li>')
				val = 'val';
				changeTodo(id,input,val);
				
			});
			
			$( ".delete" ).unbind().on('click', function() {
				idcmd = $(this).attr('alt'); 
				id = $(this).attr('name');
				changeTodo('del',idcmd,id)
				
			});
		
	

			function changeTodo(_action,_idcmd, _id) {
				$.ajax({// fonction permettant de faire de l'ajax
					type: "POST", // methode de transmission des données au fichier php
					url: "plugins/todo/core/ajax/todo.ajax.php", // url du fichier php
					data: {
						action: "changeTodo",
						acte: _action,
						idcmd: _idcmd,
						id: _id
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
					if (_action != 'check') {
					 modal(false);
        			 panel(false);
       				 page('todos', 'Todo List', '', 'todo');
					}
					
					}
				});			
			}					
		}
	});
	//displaytodo(_object_id)
	
}

function displaytodo(_object_id) {
    $.showLoading();
    $.ajax({
        type: 'POST',
        url: 'plugins/todo/core/ajax/todo.ajax.php',
        data: {
            action: 'gettodos',
            object_id: _object_id,
            version: 'mview'
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_displayEquipementTodo').empty();
            for (var i in data.result.eqLogics) {
                $('#div_displayEquipementTodo').append(data.result.eqLogics[i]).trigger('create');
            }
            setTileSize('.eqLogic');
            $('#div_displayEquipementTodo').packery({gutter : 4});
            $.hideLoading();
        }
    });
}

function page(_page, _title, _option, _plugin,_dialog) {
    $.showLoading();
    $('.ui-popup').popup('close');
    if (isset(_title) && (!isset(_dialog) || !_dialog)) {
        $('#pageTitle').empty().append(_title);
    }
    if (_page == 'connection') {
        var page = 'index.php?v=m&ajax=1&p=' + _page;
        $('#page').load(page, function () {
            $('#page').trigger('create');
            $('#pagecontainer').css('padding-top','64px');
            setTimeout(function(){$('#pagecontainer').css('padding-top','64px');; }, 100);
        });
        return;
    }

    jeedom.user.isConnect({
        success: function (result) {
            if (!result) {
                initApplication(true);
                return;
            }
            var page = 'index.php?v=m&ajax=1&p=' + _page;
            if (init(_plugin) != '') {
                page += '&m=' + _plugin;
            }
            if(isset(_dialog) && _dialog){
                $('#popupDialog .content').load(page, function () {
                    var functionName = '';
                    if (init(_plugin) != '') {
                        functionName = 'init' + _plugin.charAt(0).toUpperCase() + _plugin.substring(1).toLowerCase() + _page.charAt(0).toUpperCase() + _page.substring(1).toLowerCase();
                    } else {
                        functionName = 'init' + _page.charAt(0).toUpperCase() + _page.substring(1).toLowerCase();
                    }
                    if ('function' == typeof (window[functionName])) {
                        if (init(_option) != '') {
                            window[functionName](_option);
                        } else {
                            window[functionName]();
                        }
                    }
                    Waves.init();
                    $("#popupDialog").popup({
                        beforeposition: function () {
                            $(this).css({
                                width: window.innerWidth - 40,
                            });
                        },
                        x: 5,
                        y: 70
                    });
                    $('#popupDialog').popup('open');
                });
}else{
    $('#page').hide().load(page, function () {
        $('#page').trigger('create');

        var functionName = '';
        if (init(_plugin) != '') {
            functionName = 'init' + _plugin.charAt(0).toUpperCase() + _plugin.substring(1).toLowerCase() + _page.charAt(0).toUpperCase() + _page.substring(1).toLowerCase();
        } else {
            functionName = 'init' + _page.charAt(0).toUpperCase() + _page.substring(1).toLowerCase();
        }
        if ('function' == typeof (window[functionName])) {
            if (init(_option) != '') {
                window[functionName](_option);
            } else {
                window[functionName]();
            }
        }
        Waves.init();
        $('#pagecontainer').css('padding-top','64px');
        $('#page').fadeIn(400);
        setTimeout(function(){$('#pagecontainer').css('padding-top','64px');; }, 100);
    });
}
}
});
}
