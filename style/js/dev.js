var dev = {
	tickets: [],
	modules: {},
	ticketsByPriority: {},
	ticketsWithoutModule: {},
	priorities: {},
	statuses: {},
	types: {},
	
	displayByModulesCache: '',
	displayByPrioritiesCache: '',
	displayByTimeCache: '',
	
	addTicket: function(ticket){
		this.tickets[ticket.id] = ticket;
		if(ticket.module)
		{
			this.modules[ticket.module].childs[-ticket.id] = ticket;
		} else {
			this.ticketsWithoutModule[-ticket.id] = ticket;
		}
		this.ticketsByPriority[ticket.priority][-ticket.id] = ticket;
	},
	addModule: function(module){
		this.modules[module.id] = module;
	},
	addPriority: function(id, title){
		this.priorities[id] = title;
		this.ticketsByPriority[id] = {};
	},
	
	
	ticket: function(id, title, type, priority, date, module){
		this.id = id;
		this.title = title;
		this.type = type;
		this.priority = priority;
		this.date = date;
		this.module = module;
	},
	
	module: function(id, title, type, priority, date){
		this.id = id;
		this.title = title;
		this.type = type;
		this.priority = priority;
		this.date = date;
		this.childs = {};
	},
	
	displayByModules: function(obj){
		if(!this.displayByModulesCache)
		{
			for(i in this.ticketsWithoutModule){
				this.displayByModulesCache += this.ticketsWithoutModule[i].html();
			}
			for(i in this.modules) {
				this.displayByModulesCache += this.modules[i].html();
				var m = this.modules[i];
				for(j in m.childs)
				{
					this.displayByModulesCache += m.childs[j].html();
				}
			}
		}
		$(obj).set('html', this.displayByModulesCache);
	},
	
	displayByPriorities: function(obj){
		if(!this.displayByPrioritiesCache){
			for(k in this.ticketsByPriority){
				var showed = false;
				for(j in this.ticketsByPriority[k]) {
					if(!showed)
					{
						this.displayByPrioritiesCache += this.priorityHtml(k);
						showed = true;
					}
					this.displayByPrioritiesCache += this.ticketsByPriority[k][j].html();
				}
			}
		}
		$(obj).set('html', this.displayByPrioritiesCache);
	},
	
	priorityHtml: function(id){
		return "<div class=\"priority prior-"+id+"\">"+dev.priorities[id]+"</div>";
	},
	
	displayByTime: function(obj){
		if(!this.displayByTimeCache){
			for(k in this.tickets.sort(
				function(a,b){
					if(a && b) return a.id>b.id?-1:(a.id==b.id?0:1);
					return a?1:-1;
				})) if(this.tickets[k] && this.tickets[k].html){
				this.displayByTimeCache += this.tickets[k].html();
			}
		}
		$(obj).set('html', this.displayByTimeCache);
	}
}

dev.ticket.prototype = {
	htmlCache: '',
	html: function() {
		if(!this.htmlCache)
		{
			this.htmlCache += '<div class="ticket prior-'+this.priority+'">';
			// tiny pic here
			this.htmlCache += '['+dev.types[this.type]+']';
			this.htmlCache += '<span class="ticket-id">'+this.id+'</span>';
			this.htmlCache += '<a href="/ticket-'+this.id+'.xml">'+this.title+'</a>';
			// assignee here
			this.htmlCache += '</div>';
		}
		return this.htmlCache;
	}
};
	
dev.module.prototype = {
	htmlCache: '',
	html: function() {
		if(!this.htmlCache)
		{
			this.htmlCache += '<div class="module prior-'+this.priority+'">';
			// tiny pic here
			this.htmlCache += '['+dev.types[this.type]+']';
			this.htmlCache += '<span class="ticket-id">'+this.id+'</span>';
			this.htmlCache += '<a href="/ticket-'+this.id+'.xml">'+this.title+'</a>';
			// assignee here
			this.htmlCache += '</div>';
		}
		return this.htmlCache;
	}
};