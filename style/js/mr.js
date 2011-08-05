var mr_Ajax_timeout;

var mr_Ajax_defaults = {
	onSuccess: function(responseText, responseXML){
		$('ajax-process').set('html', '<img src="http://mir.io/style/img/ajax/success.gif" width="60" height="16" alt="" title="Успешно"/>');
		$clear(mr_Ajax_timeout);
		mr_Ajax_timeout = setTimeout("$('ajax-process').set('html', '&nbsp;');", 3000);
	},
	onFailure: function(instance){
		$('ajax-process').set('html', 'Ошибка!');
		$clear(mr_Ajax_timeout);
		mr_Ajax_timeout = setTimeout("$('ajax-process').set('html', '&nbsp;');", 15000);
	},
	onRequest: function(instance){
		$('ajax-process').set('html', '<img src="http://mir.io/style/img/ajax/process.gif" width="60" height="16" alt="" title="Обновление..."/>');
		$clear(mr_Ajax_timeout);
	}
};

function mr_Ajax(params)
{
	return new Request.HTML( $merge(mr_Ajax_defaults, params) );
}

function mr_Ajax_Request(params)
{
	return new Request( $merge(mr_Ajax_defaults, params) );
}

function mr_Ajax_Form(form, params)
{
	return mr_Ajax( $merge({url:form.getAttribute('action')}, params) ).post( form );
}

function ajax_login_request(sid)
{
  if(!$defined(ajax_login)) var ajax_login = mr_Ajax({url:'/x/ajax-login/in', evalScripts:true, method:"GET"});
  ajax_login.send();
  setTimeout('ajax_login_request()', 1000*80);
}

window.addEvent('domready', function(e){

	new SmoothScroll();

});

var shadow_item = null;
var shadow_displayed = false;

function shadow(force_display)
{
	if(Browser.Engine.trident) return;
	if(!shadow_item)
	{
		shadow_item = new Element("div", {
			id: 'shadow'
		});
		shadow_item.style.top = $('main-menu').getCoordinates().height+"px"; 
		shadow_item.fade("hide");
		$('container').adopt(shadow_item);
	}
	if(shadow_displayed && !force_display)
	{
		shadow_item.fade("out");
		shadow_displayed = false;
	} else {
		shadow_item.style.width = $('container').getScrollSize().x;
		shadow_item.style.height = $('container').getScrollSize().y-$('main-menu').getCoordinates().height;
		shadow_item.fade("0.4");
		shadow_displayed = true;
	}
}

var sidenav_showed;
var sidenav_omit_overloading={};

function mr_sidenav(item)
{
	var curitem = $('sidenav-'+item);

	if(!curitem.subitem)
	{
		curitem.subitem = new Element("div", {
			'id': 'subsidenav-'+item,
			'class': 'subsidenav'
		});
		curitem.subitem.style.position = 'absolute';
		curitem.subitem.fade('hide');
		var coords = curitem.getCoordinates(curitem.getParent());

		curitem.getParent().adopt(curitem.subitem);

		curitem.subitem.style.left = 30+"px";
		curitem.subitem.style.top = coords.top+"px";
	}
	if(!curitem.subshowed)
	{
		if(sidenav_showed && sidenav_showed != curitem)
		{
			sidenav_showed.subitem.fade(0);
			sidenav_showed.subshowed = false;
		}
		sidenav_showed = curitem;

		if(!sidenav_omit_overloading[item])
		mr_Ajax({url:'/x/site-sidenav/'+item, update:curitem.subitem, method:"GET"}).send();

		curitem.subshowed=true;
		shadow(true);
		curitem.subitem.fade(1);
	} else {
		curitem.subshowed = false;
		if(sidenav_showed==curitem) sidenav_showed = false;
		shadow();
		curitem.subitem.fade(0);
	}

}

 function float_ch(el)
 {
 	if(el.className=='float-hide')
 	{
 		el.className='float-show';
 		$(el).get('tween', {property: 'width', duration: 300, transition: Fx.Transitions.Sine.easeOut}).start(220);
 		return true;
 	} else {
 		el.className='float-hide';
 		$(el).get('tween', {property: 'width', duration: 100, transition: Fx.Transitions.Sine.easeIn}).start(14);
 		return false;
 	}
 }

function format_sel(update, tagName, nL) {
  var str = document.selection.createRange().text;
  if(!str) return;
  $(update).focus();
  var sel = document.selection.createRange();
  if(str.indexOf("\n")>0) nL=true;
  newLine = nL?"\n":"";
  sel.text = newLine + "[" + tagName + "]" + newLine + str + newLine + "[/" + tagName + "]" + newLine;
  return;
}

function insert_link(update) {
  var str = document.selection.createRange().text;
  $(update).focus();
  var my_link = prompt("Введите адрес:");
  if (my_link != null) {
    var sel = document.selection.createRange();
	sel.text = "[url href=\"" + my_link + "\"]" + str + "[/url]";
  }
  return;
}

 function text_markup(update, attach, crease, insert)
 {
 	el_html = '';
 	if(!insert)
 	{
 		el_html = "<br/>";
 	}
 	if(attach != 0)
 	{
 		el_html += ("<a href=\"javascript:void(0)\" onClick=\"javascript:window.open('/x/site-attach/form?update="+update+"','','menubar=0,scrollbars=1,status=0,width=350,height=200'); return false\">Добавить вложение</a>");
		el_html += (" &nbsp; ");
 	}

 	if(crease)
 	{
 		el_html += ("<a href=\"javascript:void(0)\" title=\"Складка\" onclick=\"javascript:format_sel('"+update+"','crease',1)\">Складка</a>");
		el_html += (" &nbsp; ");
 	}

	el_html += ("<a href=\"javascript:void(0)\" title=\"Наклонный текст\" onclick=\"javascript:format_sel('"+update+"','i')\">[<i>i</i>]</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"Полужирный текст\" onclick=\"javascript:format_sel('"+update+"','b')\">[<b>b</b>]</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"Зачёркнутый текст\" onclick=\"javascript:format_sel('"+update+"','s')\">[<s>s</s>]</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"Подчёркнутый текст\" onclick=\"javascript:format_sel('"+update+"','u')\">[<u>u</u>]</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"Осветлённый текст\" onclick=\"javascript:format_sel('"+update+"','ot')\">[<span style=\"color:gray\">ot</span>]</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"Ссылка - выделите текст, который станет ссылкой\" onclick=\"javascript:insert_link('"+update+"')\">[url]</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"По левому краю\" onclick=\"javascript:format_sel('"+update+"','align-left',1)\">лево</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"По центру\" onclick=\"javascript:format_sel('"+update+"','align-center',1)\">центр</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"По правому краю\" onclick=\"javascript:format_sel('"+update+"','align-right',1)\">право</a>");
		el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"Подзаголовок\" onclick=\"javascript:format_sel('"+update+"','c')\">подзаголовок</a>");
	el_html += (" &nbsp; ");
	el_html += ("<a href=\"javascript:void(0)\" title=\"Предпросмотр (вложения не отображаются! форматирование - проза!)\" onclick=\"javascript:window.open('/x/site-preview/get?id="+update+"','','menubar=0,scrollbars=1,status=0,width=600,height=450'); return false\">/ предпросмотр</a>");

	if(insert)
	{
		$(insert).set('html', el_html);
	} else {
		document.write(el_html);
	}
}