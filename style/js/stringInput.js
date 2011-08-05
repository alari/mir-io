function stringInput(inputId, inputDivId, url, searchString){
	this.url = url;
	this.searchString = searchString;
	
	this.divId = inputDivId;
	this.divEl = $(this.divId);
	
	this.divEl.style.visibility = 'hidden';
	this.divEl.style.position = 'absolute';
		
	this.inputId = inputId;
	this.inputEl = $(this.inputId);
	this.inputEl.stringInput = this;
	this.inputEl.onkeyup = 'this.stringInput.check(this.value)';
	this.inputEl.onfocus = 'this.onkeyup()';
	this.inputEl.onblur = 'this.stringInput.blur(1)';
		
	this.divEl.stringInput = this;
	this.divEl.addEvent("click", function(e){
		$clear(this.timeout);
	});
}

stringInput.prototype = {
	ajax: null,
	timeout: null,
	cache: {},
	inputId: null,
	inputEl: null,
	divId: null,
	divEl: null,
	currentString: null,
	url: null,
	searchString: null,
	
	check: function(str) {
		if(!str || str.length <2) return this.blur();
  			
		if(str == this.currentString) return;
		this.currentString = str;
		
  		if(this.ajax) this.ajax.cancel();
  			
  		this.divEl.set('html', this.searchString);
  		this.divEl.style.visibility = 'visible';
  			
  		if(this.cache[str])
  		{
  			this.divEl.set('html', this.cache[str]);
  		} else {
	  		this.ajax = mr_Ajax(
	  			{
	  				url:this.url,
	  				data:
	  				{
	  					str:str,
	  					insert:this.inputId
	  				},
	  				update:this.divEl
	  			}).send();
  		}
  			
  		$(document.body).addEvent("click", this.bodyEvent.bindWithEvent(this));
	},
	
	blur: function(wait){
		if(wait)
  		{
  			this.timeout = this.blur.delay(200, this);
  			return;
  		}
  		this.divEl.set('html', '');
  		this.divEl.style.visibility = 'hidden';
  		$(document.body).removeEvent("click", this.bodyEvent);
	},
	
	bodyEvent: function(e){
		var target = $(e.target);
  		if(target == this.divEl || this.divEl.hasChild(target)) return;
  		this.blur();
	},
	
	cacheDiv: function(str){
		this.cache[str] = this.divEl.get('html');
	}
}