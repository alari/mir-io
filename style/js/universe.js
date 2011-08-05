  var Planets = new Array();
  
  function movePlanets()
  {
  	for(var i in Planets)
  	{
  		if(i>=0 && !Planets[i].stopped)
  			Planets[i].move();
  	}
  }
  
  var Planet = new Class({
  	initialize: function(parent, radius, size, w, center_x, center_y, fi){
  		this.PlanetID = Planets.length?Planets.length:0;
  		Planets[ this.PlanetID ] = this;
  		
  		this.radius = radius;
  		this.size = size;
  		this.w = w;
  		this.c_x = center_x;
  		this.c_y = center_y;
  		this.t = fi/w;
  		
  		this.el = new Element('div', {'class':'planet'});
  		parent.adopt(this.el);
  		this.el.style.width = this.size;
  		this.el.style.height = this.size;
  		
  		this.el.planet = this;
  		
  		this.el.addEvent('mouseenter', function(ev){
  			this.planet.mover();
  		});
  		this.el.addEvent('mouseleave', function(ev){
  			this.planet.mout();
  		});
  		
  		this.stopped = false;
  	},
  	move: function(){
  		this.el.style.left = this.getX();
  		this.el.style.top = this.getY();
  		
  		this.t ++;
  	},
  	getX: function(){
  		return this.c_x - this.size/2 + this.radius * Math.cos( this.t * this.w );
  	},
  	getY: function(){
  		return this.c_y - this.size/2 + this.radius * Math.sin( this.t * this.w );
  	},
  	mover: function(){
  		
  		this.stopped = true;
  		
  		this.el.style.width = this.size*2;
  		this.el.style.height = this.size*2;
  		this.el.style.left = this.getX()-this.size/2;
  		this.el.style.top = this.getY()-this.size/2;
  		this.el.toggleClass("planet-over");
  		
  		if(this.anchor)
  		{
  			this.anchor.style.left = this.size - this.anchor.offsetWidth/2;
  			this.anchor.style.top = this.size - this.anchor.offsetHeight/2;
  		}
  	},
  	mout: function(){
  		
  		this.stopped = false;
  		
  		this.el.style.width = this.size;
  		this.el.style.height = this.size;
  		this.el.style.left = this.getX();
  		this.el.style.top = this.getY();
  		this.el.toggleClass("planet-over");
  	},
  	setLink: function(text, href){
  		this.anchor = new Element('a', {href: href});
  		this.el.adopt(this.anchor);
  		this.anchor.setHTML(text);
  	},
  	setImage: function(url){
  		this.el.style.background = "none";
  		this.el.style.border = "none";
  		
  		this.img = new Element('img');
  		this.el.adopt(this.img);
  		this.img.src = url;
  	},
  	setID: function(id)
  	{
  		this.el.id = id;
  	}
  });
  
  
  
  var Star = new Class({
  	initialize: function(parent, center_x, center_y, size){
  		this.el = new Element('div', {'class':'star'});
  		parent.adopt(this.el);
  		
  		this.size = size;
  		this.c_x = center_x;
  		this.c_y = center_y;
  		
  		this.el.style.width = this.size;
  		this.el.style.height = this.size;
  		
  		this.el.style.left = this.c_x - this.size/2;
  		this.el.style.top = this.c_y - this.size/2;
  		
  		this.el.star = this;
  	},
  	makePulse: function(period, min){
  		this.period = period;
  		this.min = min;
  		
  		this.effect = new Fx.Styles(this.el, {
  			duration: this.period,
  			transition: Fx.Transitions.Quad.easeInOut
  		});
  		  		
  		this.effect.start({
  			height: this.size*this.min,
  			width: this.size*this.min,
  			left: this.c_x-this.size*this.min/2,
  			top: this.c_y-this.size*this.min/2
  		}).chain(function(){
  			
  		this.element.star.effect.start({
  			height: this.element.star.size,
  			width: this.element.star.size,
  			left: this.element.star.c_x-this.element.star.size/2,
  			top: this.element.star.c_y-this.element.star.size/2
  		});
  		
  		});	
  	},
  	setImage: function(url)
  	{
  		this.el.style.background = "none";
  		this.el.style.border = "none";
  		
  		this.img = new Element('img');
  		this.el.adopt(this.img);
  		this.img.src = url;
  	}
  });