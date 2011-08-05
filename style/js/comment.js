function comment_delete(type, id)
{
	if(!confirm("Вы уверены в желании необратимого удаления этого комментария?")) return;
	
	$('c'+id).style.border = '2px solid darkred';
	
	mr_Ajax({url:'/x/ajax-comment/'+type+'/'+id+'/delete', method: 'get'}).send();
}

var comment_oldcontent = {};

function comment_edit(type, id)
{
	if(comment_oldcontent[id]) return comment_edit_refuse(id);
	var c = $('c'+id+'content');
	comment_oldcontent[id] = c.innerHTML;
	
	mr_Ajax({url:'/x/ajax-comment/'+type+'/'+id+'/edit/content', method: 'get', update: $('c'+id+'content')}).send();
}

function comment_edit_refuse(id)
{
	$('c'+id+'content').innerHTML = comment_oldcontent[id];
	comment_oldcontent[id] = null;
}

function comment_hide(type, id)
{
	mr_Ajax({url:'/x/ajax-comment/'+type+'/'+id+'/hide', method: 'get'}).send();
}