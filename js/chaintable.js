TableKit.Sortable.addSortType(new TableKit.Sortable.Type('status', {
		pattern : /^[New|Assigned|In Progress|Closed]$/,
		normal : function(v) {
			var val = 4;
			switch(v) {
				case 'New':
					val = 0;
					break;
				case 'Assigned':
					val = 1;
					break;
				case 'In Progress':
					val = 2;
					break;
				case 'Closed':
					val = 3;
					break;
			}
			return val;
		}
	}
));


// function splits up printed date and gives requested portion
function resDate(str,no){
	//return str;
    var dates = str.split(" - ");
    
    if (dates[no]){
    	return dates[no].substr(0,(dates[no].length-2));
    }
    
    return "";
    
}

TableKit.Editable.dateSpanEditor = Class.create();
TableKit.Editable.dateSpanEditor.prototype = {
	initialize : function(name, options){
		this.name = name;
		this.options = Object.extend({
			element : 'input',
			attributes1 : {name : 'beg', type : 'text'},
			attributes2 : {name : 'end', type : 'text'},
			selectOptions : [['BC',0],['AD',1],['BP',2]],
			showSubmit : true,
			submitText : 'OK',
			showCancel : true,
			cancelText : 'Cancel',
			ajaxURI : '/api.php',
			ajaxOptions : null
		}, options || {});
	},
	edit : function(cell) {
		cell = $(cell);
		var op = this.options;
		var table = cell.up('table');

		var form = $(document.createElement("form"));
		form.id = cell.id + '-form';
		form.addClassName(TableKit.option('formClassName', table.id)[0]);
		form.onsubmit = this._submit.bindAsEventListener(this);

		var field1 = document.createElement(op.element);
		
        var field2 = document.createElement(op.element);
			$H(op.attributes1).each(function(v){
				field1[v.key] = v.value;
			});
			
		var text = TableKit.getCellText(cell);

            field1.value = resDate(text,0);
            field1.className="dateinput";
            
			$H(op.attributes2).each(function(v){
				field2[v.key] = v.value;
			});
			
			field2.value = resDate(text,1);
            field2.className="dateinput";
            
        var dd1=document.createElement('select');
            dd1.name="begpref";
            
			$A(op.selectOptions).each(function(v){
				dd1.options[dd1.options.length] = new Option(v[0], v[1]);
			});
			

			dd1.options[0].selected = true;
			
	    var dd2=document.createElement('select');
            dd2.name="endpref";
            
			$A(op.selectOptions).each(function(v){
				dd2.options[dd2.options.length] = new Option(v[0], v[1]);
			});
			
			dd2.options[0].selected = true;
            
            form.appendChild(field1); 
            form.appendChild(dd1); 
            form.appendChild(field2);
            form.appendChild(dd2); 

			if(op.showSubmit) {
				var okButton = document.createElement("input");
				okButton.type = "submit";
				okButton.value = op.submitText;
				okButton.className = 'editor_ok_button';
				form.appendChild(okButton);
			}
			if(op.showCancel) {
				var cancelLink = document.createElement("a");
				cancelLink.href = "#";
				cancelLink.appendChild(document.createTextNode(op.cancelText));
				cancelLink.onclick = this._cancel.bindAsEventListener(this);
				cancelLink.className = 'editor_cancel';      
				form.appendChild(cancelLink);
			}
			cell.innerHTML= "";
			cell.appendChild(form);
	},
	_submit : function(e) {
		var cell = Event.findElement(e,'td');
		var form = Event.findElement(e,'form');
		Event.stop(e);
		this.submit(cell,form);
	},
	submit : function(cell, form) {
		var op = this.options;
		form = form ? form : cell.down('form');
		var head = $(TableKit.getHeaderCells(null, cell)[TableKit.getCellIndex(cell)]);
		var row = cell.up('tr');
		var table = cell.up('table');
        var rawbeg = parseInt(form.beg.value);
        var rawend = parseInt(form.end.value);
				
		// Sort Dates
		switch (form.begpref.value){
		case '0':
			// beg is beg+2000
			beg = rawbeg+2000;
			begsuf = "BC";
			break;
		case '1':
			// beg is 2000-beg
			beg = 2000-rawbeg;
			begsuf = "AD";
			break;
		case '2':
			// beg is beg
			beg= rawbeg;
			begsuf = "BP";
			break;
		}
		
		switch (form.endpref.value){
		case '0':
			// end is end+2000
			endsuf = "BC";
			end = rawend+2000;
			break;
		case '1':
			// end is 2000-end
			end = 2000-rawend;
			endsuf = "AD";
			break;
		case '2':
			// ned is end
			end=rawend;
			endsuf = "BP";
			break;
		}
		
        var ajaxurl =  "api.php";
		
        // var ajaxurl = "/trunk_working/api.php";
		
		var item_key = "cor_tbl_" + row.id.split("-")[0];
		
		var itemval = row.id.split("-")[1];
		
		var fieldname = head.id.split("_")[2];
		
		
		if (!isNaN(cell.id.split("-")[2])){
			var qtype = "add";
			var edt_id = null;
		} else {
			var qtype = "edt";
			var edt_id = cell.id.split("-")[3];
		}

		
		var s = '?req=putField&'+fieldname+'_qtype=' + qtype + '&beg=' + beg + '&end=' + end + '&itemkey=' + item_key + '&field=' + head.id + '&itemval=' + itemval + '&'+fieldname+'_id=' + edt_id;
		
		if (!isNaN(rawbeg) || !isNaN(rawend)){
			this.ajax = new Ajax.Request(ajaxurl+s, {
				onSuccess: function(response) {
					
					cell.innerHTML= "<span class=\"data\">" + rawbeg + begsuf + " - "+ rawend + endsuf + "</span>";
					




					var data = TableKit.getCellData(cell);
					data.active = false;
					data.refresh = true; // mark cell cache for refreshing, in case cell contents has changed and sorting is applied
					
					cell.id=table.id+"-cell-span-"+response.responseJSON.qry_results[0].new_id;
				}
				});
		}else{
			var error = document.createElement("span");
			error.id="dateNumberError"+cell.id;
			error.innerHTML="<br>Dates must be numeric years";
			error.className="error";
			if (!document.getElementById("dateNumberError"+cell.id)){
				form.appendChild(error);
			}
		}
	},
	_cancel : function(e) {
		var cell = Event.findElement(e,'td');
		Event.stop(e);
		this.cancel(cell);
	},
	cancel : function(cell) {
		this.ajax = null;
		var data = TableKit.getCellData(cell);
		cell.innerHTML = data.htmlContent;
		data.htmlContent = '';
		data.active = false;
	},
	ajax : null
};

TableKit.Editable.xmiEditor = Class.create();
TableKit.Editable.xmiEditor.prototype = {
	initialize : function(name, options){
		this.name = name;
		this.options = Object.extend({
			element : 'input',
			showSubmit : true,
			submitText : 'OK',
			showCancel : true,
			cancelText : 'Cancel',
			ajaxURI : '/api.php',
			ajaxOptions : null
		}, options || {});
	},
	edit : function(cell) {
		cell = $(cell);
		var op = this.options;
		var table = cell.up('table');

		var form = $(document.createElement("form"));
		form.id = cell.id + '-form';
		form.addClassName(TableKit.option('formClassName', table.id)[0]);
		form.onsubmit = this._submit.bindAsEventListener(this);

		var text = TableKit.getCellText(cell);
		var field = document.createElement(op.element);

		field.id = 'authitem_dropdown';
		field.value=text;
                   
        form.appendChild(field); 
            
        field.autocomplete="on";
        if(op.showSubmit) {
			var okButton = document.createElement("input");
			okButton.type = "submit";
			okButton.value = op.submitText;
			okButton.className = 'editor_ok_button';
			form.appendChild(okButton);
		}
		if(op.showCancel) {
			var cancelLink = document.createElement("a");
			cancelLink.href = "#";
			cancelLink.appendChild(document.createTextNode(op.cancelText));
			cancelLink.onclick = this._cancel.bindAsEventListener(this);
			cancelLink.className = 'editor_cancel';      
			form.appendChild(cancelLink);
		}
		
		cell.innerHTML= "";
		cell.appendChild(form);

		jQuery( "#authitem_dropdown" ).autocomplete({
			source: availableTags
			});
	},
	_submit : function(e) {
		var cell = Event.findElement(e,'td');
		var form = Event.findElement(e,'form');
		Event.stop(e);
		this.submit(cell,form);
	},
	submit : function(cell, form) {
		var op = this.options;
		form = form ? form : cell.down('form');
		var head = $(TableKit.getHeaderCells(null, cell)[TableKit.getCellIndex(cell)]);
		var row = cell.up('tr');
		var table = cell.up('table');
        var xmi = form.authitem_dropdown.value;
		
        var ajaxurl =  "api.php";
		
		var item_key = "cor_tbl_" + row.id.split("-")[0];
		
		var itemval = row.id.split("-")[1];
		
		var fieldname = head.id.split("_")[2];
		
		
		if (!isNaN(cell.id.split("-")[2])){
			var qtype = "add";
			var edt_id = null;
		} else {
			var qtype = "edt";
			var edt_id = cell.id.split("-")[3];
		}

		
		var s = '?req=putField&xmi_list_cat_qtype=' + qtype + '&ste_cd=METSUR&xmi_itemkey=cor_tbl_number' + '&xmi_list_cat=' + xmi + '&itemkey=' + item_key + '&field=' + head.id + '&itemval=' + itemval + '&'+fieldname+'_id=' + edt_id;
		
		if (jQuery.inArray(xmi,availableTags)!=-1||xmi==""){
			this.ajax = new Ajax.Request(ajaxurl+s, {
				onSuccess: function(response) {
					
					span = document.createElement("span");
					span.className="data";
					span.innerHTML="<a class=\"itemkey_link\" href=\"/metaponto/ark/micro_view.php?item_key=cat_cd&cat_cd="+xmi+"\">"+xmi+"</a>";
					cell.innerHTML="";
					cell.appendChild(span);

					cell.id=table.id+"-cell-span-"+response.responseJSON.qry_results[0].new_id;

					var data = TableKit.getCellData(cell);
					data.active = false;
					data.refresh = true; // mark cell cache for refreshing, in case cell contents has changed and sorting is applied
					cell.id=table.id+"-cell-span-"+response.responseJSON.qry_results[0].new_id;
					cell.onClick=("function () { Alert(\"Reload page to make this cell editable\"");

				}
				});
		}else{
			var error = document.createElement("span");
			error.id="dateNumberError"+cell.id;
			error.innerHTML="<br>linked item must exist";
			error.className="error";
			if (!document.getElementById("dateNumberError"+cell.id)){
				form.appendChild(error);
			}
		}
	},
	_cancel : function(e) {
		var cell = Event.findElement(e,'td');
		Event.stop(e);
		this.cancel(cell);
	},
	cancel : function(cell) {
		this.ajax = null;
		var data = TableKit.getCellData(cell);
		cell.innerHTML = data.htmlContent;
		data.htmlContent = '';
		data.active = false;
	},
	ajax : null
};


TableKit.Editable.addCellEditor(
	new TableKit.Editable.dateSpanEditor('conf_field_finddates', {
		element : 'input',
	})
);

TableKit.Editable.addCellEditor(
		new TableKit.Editable.xmiEditor('conf_field_catxmiloc', {
			element : 'input',
		})
	);



TableKit.Editable.selectInput('name', {}, [
			['1','1'],
			['2','2'],
			['3','3'],
			['4','4'],
			['5','5']																												
		]);

function deletefrag( frag_id, field ) {
	  var answer = confirm('Are you sure you want to delete this record?');
	  if (answer) {

		  deleteArray= new Array;
		  row = document.getElementById(frag_id+"-row");
		  i=0;
		  // loop over cells
		  while (i<row.cells.length){
			  row.cells[i].innerHTML="<span class=\"error\">DELETING...</span>";
			  new Ajax.Request(
					  'api.php?req=putField&update_db=delfrag&delete_qtype=del&dclass='+row.cells[i].id.split("-")[2]+'&frag_id=' + row.cells[i].id.split("-")[3]+'&field='+field,{
						  onSuccess: function(response) {
						  }      

					  }
			  );
			  i++

		  }
		  // after final cell reload page
		  new Ajax.Request(
				  'api.php?req=putField&update_db=delfrag&delete_qtype=del&dclass='+frag_id.split("-")[0]+'&frag_id=' + frag_id.split("-")[1]+'&field='+field,{
					  onSuccess: function(response) {
						  window.location.reload(true);
					  }      

				  }
		  );

		  
	    return false;
	  }
}

