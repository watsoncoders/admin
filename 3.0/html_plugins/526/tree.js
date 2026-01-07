(function($){
	$(function(){

		var deleteCode = '<span class="delete ui-button"><i class="fa fa-trash-o" aria-hidden="true" title="מחיקה"></i></span>';
		var editCode = '<span class="edit ui-button"><i class="fa fa-pencil-square-o" aria-hidden="true" title="עדכון"></i></span>';
		var addChildCode = '<span class="addChild ui-button"><i class="fa fa-code-fork" aria-hidden="true" title="הוספת בן"></i></span>';
		var addSiblingCode = '<span class="addSibling ui-button"><i class="fa fa-plus-circle" aria-hidden="true" title="הוספת אח"></i></span>';
		var cancelCode = '<span class="cancel ui-button"><i class="fa fa-times" aria-hidden="true" title=""></i></span>';

		var rootButtons = addChildCode;
		var standardButtons = addChildCode + addSiblingCode + deleteCode + editCode;
		var customButtons = addChildCode + addSiblingCode + deleteCode + editCode;
		var cancelButtons = cancelCode;

		var showError = function(message){
			alert(message);
		}

		var idRegexp = /onclickOptionTree\(.*?,(\d+),(\d+),(\d+)\)/;

		var deleteLink = function(elem){
			var rootElem = elem.parent().parent();
			elem.remove();
			var childList = rootElem.find('ul.line-tree li');
			if (!childList.length){
				rootElem.children('ul.line-tree').remove();
				rootElem.children('a.insExtra').remove();
			}
		}

		var getLinkId = function(elem){
			var link = elem.children('a.paraplus');
			if (!link.length) return 0;
			var href = link.attr('href');
			if (href == 'javascript:void(0)') return 0;
			var id = href.match(idRegexp)[1];
			return id;
		}

		var getParentId = function(elem){
			var link = elem.children('a.paraplus');
			if (!link.length) return 0;
			var href = link.attr('href');
			if (href == 'javascript:void(0)') return 0;
			var parentId = href.match(idRegexp)[2];
			return parentId;
		}

		var getSiblingId = function(elem){
			var link = elem.children('a.paraplus');
			if (!link.length) return 0;
			var href = link.attr('href');
			if (href == 'javascript:void(0)') return 0;
			var parentId = href.match(idRegexp)[3];
			return parentId;
		}

		var getNewLink = function(name, id, parentId, afterSibling)
		{
			return $('<li><a href="javascript:onclickOptionTree(\'' + name + '\',' + id + ',' +parentId+ ',' +afterSibling+ ')" class="paraplus added">'+name+'</a></li>');
		}

		var nameRegexp = /'(.*?)'/;

		var cancelEditLinkName = function(elem){
			return function(){
				if (!elem.children('a.paraplus').html()) deleteLink(elem);
				else {
					elem.children('input.editAdded, .ui-button').remove();
					elem.children('a.paraplus').css('display', 'inline').after(customButtons);
				}
			}
		}

		var replaceLinkName = function(elem){
			return function(e){
				if (e.keyCode != 13) return;

				var input = elem.children('input.editAdded');
				if (!input.val())
				{
					elem.children('.delete').click();
					elem.remove();
					return false;
				}
					
				$.getJSON('treeAjax.php?action=edit&value=' + getLinkId(elem) + '&parentId=' + getParentId(elem) + '&afterSibling=' + getSiblingId(elem) +
						  '&name=' + encodeURI(input.val()),function(data)
				{
					if (data.status == 'OK')
					{
						var link = elem.children('a.paraplus');
						elem.children('input.editAdded').remove();
						link.attr('href', link.attr('href').replace(nameRegexp, "'" + data.name + "'"));
						link.html(data.name).css('display', 'inline');

						elem.children('.ui-button').remove();
						elem.children('.paraplus').after(customButtons);
					} 
					else 
					{
						elem.children('input.editAdded').remove();
						elem.children('a.paraplus').css('display', 'inline');
						showError(data.message);
					}
				});
			}
		}

		var editLink = function(elem){
			elem.children('.ui-button').remove();
			var link = elem.children('a.paraplus');
			link.css('display', 'none');
			var name = link.html();
			var input = $('<input type="text" class="editAdded" value="' + name + '" />')
			            .on({'keyup': replaceLinkName(elem), 'blur': cancelEditLinkName(elem)});
			link.after(input);
			input.after(cancelButtons);
			input.focus();
		}

		$('.DefineTree ul ul li').addClass('ui-done')
		                         .children('.paraplus')
		                         .after(standardButtons);
		$('.DefineTree > li, .DefineTree > li > ul > li').addClass('ui-done')
		                   .children('.paraplus')
		                   .after(rootButtons);

		$('.DefineTree').on('click', 'li span.addSibling', function(){
			var elem = $(this).parent();
			var parentId = getLinkId(elem);
			var elem = $(this).parent();
			$.getJSON('treeAjax.php?action=nextCatId', function(data)
			{
				if (data.status == 'OK')
				{
					var linkId 	 = getLinkId(elem);
					var parentId = getParentId(elem);
					var catId 	 = parseInt(data.catId);
					var name 	 = "";
					var newElem  = getNewLink(name, catId, parentId, linkId);

					elem.after(newElem);
					editLink(newElem);
				} 
				else 
				{
					showError(data.message);
				}
			});
			return false;
		});

		$('.DefineTree').on('click', 'li span.addChild', function(){
			var elem = $(this).parent();
			var parentId = getLinkId(elem);
			$.getJSON('treeAjax.php?action=nextCatId', function(data)
			{
				if (data.status == "OK")
				{
					if (!elem.children('ul.panel-collapse').length)
					{
						var parentId = getLinkId(elem);
						var collapseElem = $('<a class="insExtra collapsed" data-toggle="collapse" href="#collapse' + parentId + '"></a>');
						var collapseList = $('<ul id="collapse' +parentId+ '" class="panel-collapse line-tree collapse in" aria-expanded="true"></ul>');
						elem.prepend(collapseElem).append(collapseList);
					} 
					else 
					{
						var parentId = getLinkId(elem);
						var collapseList = elem.children('ul.panel-collapse');
						collapseList.addClass('in').attr('aria-expanded', 'true').css('height', '');
					}
					var catId 	= parseInt(data.catId);
					var name 	= "";
					var newElem = getNewLink(name, catId, parentId, 0);
					collapseList.append(newElem);
					editLink(newElem);
				} 
				else 
				{
					showError(data.message);
				}
			});
			return false;
		});

		$('.DefineTree').on('click', 'li span.delete', function(){
			var elem = $(this).parent();
			var id = getLinkId(elem);
			$.getJSON('treeAjax.php?action=delete&value=' + id, function(data){
				if (data.status == 'OK'){
					deleteLink(elem);
				} else {
					showError(data.message);
				}
			});
			return false;
		});

		$('.DefineTree').on('click', 'li span.edit', function(){
			var elem = $(this).parent();
			editLink(elem);
		});

		$('.DefineTree').on('click', 'li span.cancel', function(){
			var elem = $(this).parent();
			cancelEditLinkName(elem)();
		});
	});
}(jQuery));

function onclickOptionTree (name,catId)
{
	$.getJSON("treeAjax.php?action=checkAddExpertCat&expertId=" + global_expertId + "&value=" + catId, function(data)
	{
		if (data.status == "OK")
		{
			if (confirm ("אשר הוספת הקטגוריה למומחה"))
			{
				$.getJSON("treeAjax.php?action=addExpertCat&expertId=" + global_expertId + "&value=" + catId, function(data)
				{
					if (data.status == "OK")
					{
						$(".clRmvse").append("<p class='pargrph' id='expertCat_" + catId + "' catId='"+catId+"'><i class='fa fa-times iconrtl deleteRmv'></i> <span>"+name+"</span> </p>");
					}
				});
			}
		}
		else
		{
			alert (data.message);
		}
	});
//		$("#catIds").val($("#catIds").val()+catId+",");
}

