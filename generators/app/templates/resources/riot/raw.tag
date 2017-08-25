<raw>

	this.root.innerHTML = this.opts.content;

	this.on('update', function() {
		this.root.innerHTML = this.opts.content;
	});

</raw>