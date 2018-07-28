function createTodo(action,name,id) {
	if (name == '') {
		return
	}
	changeTodo(action,name,id);
}

function changeTodo(_action,_idcmd, _id) {
	$.ajax({// fonction permettant de faire de l'ajax
		type: "POST", // methode de transmission des données au fichier php
		url: "plugins/todo/core/ajax/todo.ajax.php", // url du fichier php
		global:false,
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
			
			loadData(data.result);
			if (_action == 'new') {
				 $('#'+_id).val('');
			}
		}
	});			
}	
function loadData(_id) {
	//console.log('loadData ' + _id);
	$.ajax({// fonction permettant de faire de l'ajax
		type: "POST", // methode de transmission des données au fichier php
		url: "plugins/todo/core/ajax/todo.ajax.php", // url du fichier php
		global:false,
		data: {
			action: "loadData",
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
			if (data.result.length != 0) {
				 var html = '',
				 autoCompleteName = [];
				 for (var k=0; k<data.result.length; k++) {
					 if(!data.result[k].configuration.type) {
						 if(data.result[k].isVisible == 1){
						      html += '<li id="'+data.result[k].id+'" class="list-group-item list_edit" style="background-color:transparent;font-size : 1.1em;text-decoration:none;"><span><input value="'+data.result[k].id+'" type="checkbox"></span><span class="name_mobile name_event_'+data.result[k].id+'" name="'+data.result[k].eqLogic_id+'" >'+data.result[k].name+'</span> <div class="actions"><img src="plugins/todo/img/delete.png" href="" name="'+data.result[k].eqLogic_id+'"  class="delete" alt="'+data.result[k].id+'"></div> </li>';
						 } else {
							  autoCompleteName.push(data.result[k].name);
						 }
					 }
				 }
				autocomplete(document.getElementById(_id), autoCompleteName);
				$('.todo[data-eqLogic_id="' + _id + '"] .list-group').empty().append(html);
				$('.todo[data-eqLogic_id="' + _id + '"] .list-group :checkbox').change(function() {
					id = $(this).val();
					if(this.checked) {
						changeTodo('check', id ,_id)
					} 
				});					
				$( '.todo[data-eqLogic_id="' + _id + '"] .btn_add' ).on('click', function() {
					id = $(this).val();
					input = $('#'+id).val();
					if (input == '') {
						return
					}
					changeTodo('new',input,id);
					
				});
				$( '.todo[data-eqLogic_id="' + _id + '"] .delete').on('click', function() {
					idcmd = $(this).attr('alt'); 
					id = $(this).attr('name');
					changeTodo('del',idcmd,id)
					
				});						 
			}
		}
	});				
}	


		
function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
        /*check if the item starts with the same letters as the text field value:*/
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) {
              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});
}		

