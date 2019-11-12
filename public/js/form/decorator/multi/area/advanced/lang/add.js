function %%FCT_NAME%% (btn, name) {
	var empty = $(btn).closest('.textarea-group').find('textarea').val() == "";   
	if (!empty) {
		var span = document.createElement("SPAN");
		span.className = "label label-primary";
		span.style     = "font-size: inherit; display: inline-block; text-align: justify; white-space: normal; padding: 1px  0px 1px 10px;";

		var value = $(btn).closest(".textarea-group").find('textarea').val();
		
		var text_node = value;
		
		if (!$.isEmptyObject($(value).contents().first()[0])) {
			text_node = $(value).contents().text();
		}
		
        if (%%LENGTH%%) {
        	text_node = text_node.substring(0,%%LENGTH%%) + (text_node.length > %%LENGTH%% ? '...' : '')
        }
        
        var btnGroup = $(btn).closest('.textarea-group').find('.btn-group');
		var text = document.createTextNode(text_node + " (" + $(btnGroup).find('button').text() + ")");

		var btn1 = document.createElement("BUTTON");
		btn1.setAttribute("style",   "border-radius:0; height: 20px; padding-top:0; padding-bottom: 0; margin-left: 5px;");
		btn1.setAttribute("class",   "btn btn-xs btn-primary");
		btn1.setAttribute("onclick", '%%MODIFY%%(this, \'' + name + '\');');
		btn1.setAttribute("type",    "button");
		btn1.setAttribute('title',   'Modifier')
		
		var icon1 = document.createElement("I");
		icon1.setAttribute("class", "glyphicon glyphicon-pencil");

		btn1.appendChild(icon1);

		var btn2 = document.createElement("BUTTON");
		btn2.setAttribute("style",   "border-radius:0; height: 20px; padding-top:0; padding-bottom: 0;");
		btn2.setAttribute("class",   "btn btn-xs btn-primary");
		btn2.setAttribute("onclick", '%%DELETE%%(this);');
		btn2.setAttribute("type",    "button");
		btn2.setAttribute('title',   'Supprimer')
		
		var icon2 = document.createElement("I");
		icon2.setAttribute("class", "glyphicon glyphicon-trash");
		
		btn2.appendChild(icon2);

		var lang = $(btn).closest('.textarea-group').find('.btn-group > button').val();
		
		var input = document.createElement("TEXTAREA");
		input.setAttribute("name", name + "[" + lang + "]");
		input.setAttribute("style", "display: none;");
		input.setAttribute("lang", lang);

		$(input).val(value);

		span.appendChild(text); 		
		span.appendChild(btn1); 
		span.appendChild(btn2); 
		span.appendChild(input);
		
		var div = $(btn).closest(".textarea-group").clone();
		
		$(div).addClass('advanced');
		$(div).html(span);

		$(div).insertBefore($(btn).closest(".textarea-group"));
		
		$(btn).closest('.textarea-group').find('.btn-group').each(function (i) {
        	$(this).find('ul li a[val=' + lang + ']').closest('li').addClass('disabled');
        });

		var elm = $(btn).closest('.textarea-group').find('.btn-group > ul li[class!="disabled"]:first a');
        if (typeof $(elm).html() != 'undefined') {
        	$(btn).closest('.textarea-group').find('.btn-group > button').val($(elm).attr('val'));
        	$(btn).closest('.textarea-group').find('textarea').attr('name', name + "[" + $(elm).attr('val') + "]");
            var textNode = $(btn).closest('.textarea-group').find('.btn-group > button').contents().first();
            textNode.replaceWith($(elm).text());
        } else {
        	$(btn).closest('.textarea-group').find('textarea').attr('disabled', 'disabled');
        	$(btn).closest('.textarea-group').hide();
        }

		$(btn).closest('.textarea-group').find('textarea:first').val("");         
		$(btn).closest('.textarea-group').find('textarea:first').focus();  
	}
}