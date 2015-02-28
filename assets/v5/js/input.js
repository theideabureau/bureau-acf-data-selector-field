/* **********************************************
     Begin data_selector.js
********************************************** */


(function($){
	
	acf.fields.data_selector = acf.field.extend({
		
		type: 'data_selector',
		
		$el: null,
		$input: null,
		$filters: null,
		$choices: null,
		$values: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize'
		},
		
		events: {
			'keypress [data-filter]': 			'submit_filter',
			'change [data-filter]': 			'change_filter',
			'keyup [data-filter]': 				'change_filter',
			'click .choices .acf-rel-item': 	'add_item',
			'click [data-name="remove_item"]': 	'remove_item'
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-data_selector');
			this.$input = this.$el.find('.acf-hidden input');
			this.$choices = this.$el.find('.choices'),
			this.$values = this.$el.find('.values');
			
			// get options
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// reference
			var self = this,
				$field = this.$field,
				$el = this.$el,
				$input = this.$input;
			
			
			// right sortable
			this.$values.children('.list').sortable({
			
				items:					'li',
				forceHelperSize:		true,
				forcePlaceholderSize:	true,
				scroll:					true,
				
				update:	function(){
					
					$input.trigger('change');
					
				}
				
			});
			
			
			this.$choices.children('.list').scrollTop(0).on('scroll', function(e){
				
				// bail early if no more results
				if( $el.hasClass('is-loading') || $el.hasClass('is-empty') ) {
				
					return;
					
				}
		
				
			});
			
			// ajax fetch values for left side
			this.fetch();
			
		},
		
		fetch: function(){
			

			// reference
			var self = this,
				$field = this.$field;
			
			
			// add class
			this.$el.addClass('is-loading');
			
			
			// abort XHR if this field is already loading AJAX data
			if( this.o.xhr ) {
			
				this.o.xhr.abort();
				this.o.xhr = false;
				
			}
			
			
			// add to this.o
			this.o.action = 'acf/fields/data_selector/get_data';
			this.o.field_key = $field.data('key');
			this.o.post_id = acf.get('post_id');
			
			
			// ready for ajax
			var ajax_data = acf.prepare_for_ajax( this.o );
			
			
			// clear html if is new query
			if( ajax_data.paged == 1 ) {
				
				this.$choices.children('.list').html('')
				
			}
			
			
			// add message
			this.$choices.children('.list').append('<p>' + acf._e('relationship', 'loading') + '...</p>');
			
			
			// get results
		    var xhr = $.ajax({
		    
		    	url:		acf.get('ajaxurl'),
				dataType:	'json',
				type:		'post',
				data:		ajax_data,
				
				success: function( json ){
					
					// render
					self.doFocus($field);
					self.render(json);
					
				}
				
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render: function( json ){
			
			// remove loading class
			this.$el.removeClass('is-loading is-empty');
			
			
			// remove p tag
			this.$choices.children('.list').children('p').remove();
			
			
			// no results?
			if( !json || !json.length ) {
			
				// add class
				this.$el.addClass('is-empty');
			
				
				// add message
				if( this.o.paged == 1 ) {
				
					this.$choices.children('.list').append('<p>' + acf._e('relationship', 'empty') + '</p>');
			
				}

				
				// return
				return;
				
			}
			
			
			// get new results
			var $new = $( this.walker(json) );
			
				
			// apply .disabled to left li's
			this.$values.find('.acf-rel-item').each(function(){
				
				$new.find('.acf-rel-item[data-id="' +  $(this).data('id') + '"]').addClass('disabled');
				
			});
			
			
			// underline search match
			if( this.o.s ) {
			
				var s = this.o.s;
				
				$new.find('.acf-rel-item').each(function(){
					
					// vars
					var find = $(this).text(),
						replace = find.replace( new RegExp('(' + s + ')', 'gi'), '<b>$1</b>');
					
					$(this).html( $(this).html().replace(find, replace) );	
									
				});
				
			}
			
			
			// append
			this.$choices.children('.list').append( $new );
			
			
			// merge together groups
			var label = '',
				$list = null;
				
			this.$choices.find('.acf-rel-label').each(function(){
				
				if( $(this).text() == label ) {
					
					$list.append( $(this).siblings('ul').html() );
					
					$(this).parent().remove();
					
					return;
				}
				
				
				// update vars
				label = $(this).text();
				$list = $(this).siblings('ul');
				
			});
			
			
		},
		
		walker: function( data ){
			
			// vars
			var s = '';
			
			
			// loop through data
			if( $.isArray(data) ) {
			
				for( var k in data ) {
				
					s += this.walker( data[ k ] );
					
				}
				
			} else if( $.isPlainObject(data) ) {
				
				// optgroup
				if( data.children !== undefined ) {
					
					s += '<li><span class="acf-rel-label">' + data.text + '</span><ul class="acf-bl">';
					
						s += this.walker( data.children );
					
					s += '</ul></li>';
					
				} else {
				
					s += '<li><span class="acf-rel-item" data-id="' + data.id + '">' + data.text + '</span></li>';
					
				}
				
			}
			
			
			// return
			return s;
			
		},
		
		submit_filter: function( e ){
			
			// don't submit form
			if( e.which == 13 ) {
				
				e.preventDefault();
				
			}
			
		},
		
		change_filter: function( e ){
			
			// vars
			var val = e.$el.val(),
				filter = e.$el.data('filter');
				
			
			// Bail early if filter has not changed
			if( this.$el.data(filter) == val ) {
			
				return;
				
			}
			
			
			// update attr
			this.$el.data(filter, val);
			
			
			// reset paged
			this.$el.data('paged', 1);
		    
		    
		    // fetch
		    this.fetch();
			
		},
		
		add_item: function( e ){
			
			// max posts
			if( this.o.max > 0 ) {
			
				if( this.$values.find('.acf-rel-item').length >= this.o.max ) {
				
					alert( acf._e('relationship', 'max').replace('{max}', this.o.max) );
					
					return;
					
				}
				
			}
			
			
			// can be added?
			if( e.$el.hasClass('disabled') ) {
			
				return false;
				
			}
			
			
			// disable
			e.$el.addClass('disabled');
			
			
			// template
			var html = [
				'<li>',
					'<input type="hidden" name="' + this.$input.attr('name') + '[]" value="' + e.$el.data('id') + '" />',
					'<span data-id="' + e.$el.data('id') + '" class="acf-rel-item">' + e.$el.html(),
						'<a href="#" class="acf-icon small dark" data-name="remove_item"><i class="acf-sprite-remove"></i></a>',
					'</span>',
				'</li>'].join('');
						
			
			// add new li
			this.$values.children('.list').append( html )
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		
		remove_item : function( e ){
			
			// vars
			var $span = e.$el.parent(),
				id = $span.data('id');
			
			
			// remove
			$span.parent('li').remove();
			
			
			// show
			this.$choices.find('.acf-rel-item[data-id="' + id + '"]').removeClass('disabled');
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
		}
		
	});
	

})(jQuery);



(function($){
	
	/*
	*  data_selector
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.data_selector = {
		
		$el : null,
		$input : null,
		$left : null,
		$right : null,
				
		o : {},
		
		timeout : null,
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find elements
			this.$input = this.$el.children('input[type="hidden"]');
			this.$left = this.$el.find('.data_selector_left'),
			this.$right = this.$el.find('.data_selector_right');
			
			
			// get options
			this.o = acf.helpers.get_atts( this.$el );
			
			
			// return this for chaining
			return this;
			
		},
		init : function(){
			
			// reference
			var _this = this;
			
			
			// is clone field?
			if( acf.helpers.is_clone_field(this.$input) )
			{
				return;
			}
			
			
			// set height of right column
			this.$right.find('.data_selector_list').height( this.$left.height() -2 );
			
			
			// right sortable
			this.$right.find('.data_selector_list').sortable({
				axis					:	'y',
				items					:	'> li',
				forceHelperSize			:	true,
				forcePlaceholderSize	:	true,
				scroll					:	true,
				update					:	function(){
					
					_this.$input.trigger('change');
					
				}
			});
			
			
			// load more
			var $el = this.$el;
			
			this.$left.find('.data_selector_list').scrollTop( 0 ).on('scroll', function(e){
				
				// validate
				if( $el.hasClass('loading') || $el.hasClass('no-results') )
				{
					return;
				}
				
				
				// Scrolled to bottom
				if( $(this).scrollTop() + $(this).innerHeight() >= $(this).get(0).scrollHeight )
				{
					var paged = parseInt( $el.attr('data-paged') );
					
					// update paged
					$el.attr('data-paged', (paged + 1) );
					
					// fetch
					_this.set({ $el : $el }).fetch();
				}
				
			});
			
			
			// ajax fetch values for left side
			this.fetch();
					
		},
		fetch : function(){
			
			// reference
			var _this = this,
				$el = this.$el;
			
			
			// add loading class, stops scroll loading
			$el.addClass('loading');
			
			
			// get results
		    $.ajax({
				url				:	acf.o.ajaxurl,
				type			:	'post',
				dataType		:	'json',
				data			:	$.extend({ 
					action		:	'acf/fields/data_selector/get_data', 
					post_id		:	acf.o.post_id,
					nonce		:	acf.o.nonce
				}, this.o ),
				success			:	function( json ){
					
					
					// render
					_this.set({ $el : $el }).render( json );
					
				}
			});
			
		},
		render : function( json ){
			
			// reference
			var _this = this;
			
			
			// update classes
			this.$el.removeClass('no-results').removeClass('loading');
			
			
			// new search?
			if( this.o.paged == 1 )
			{
				this.$el.find('.data_selector_left li:not(.load-more)').remove();
			}
			
			
			// no results?
			if( ! json || ! json.html )
			{
				this.$el.addClass('no-results');
				return;
			}
			
			
			// append new results
			this.$el.find('.data_selector_left .load-more').before( json.html );
			
			
			// next page?
			if( ! json.next_page_exists )
			{
				this.$el.addClass('no-results');
			}
							
			
			// apply .hide to left li's
			this.$left.find('a').each(function(){
				
				var id = $(this).attr('data-post_id');
				
				if( _this.$right.find('a[data-post_id="' + id + '"]').exists() )
				{
					$(this).parent().addClass('hide');
				}
				
			});
			
		},
		add : function( $a ){
			
			// vars
			var id = $a.attr('data-post_id'),
				title = $a.html();
			
			
			// max posts
			if( this.$right.find('a').length >= this.o.max )
			{
				alert( acf.l10n.data_selector.max.replace('{max}', this.o.max) );
				return false;
			}
			
			
			// can be added?
			if( $a.parent().hasClass('hide') )
			{
				return false;
			}
			
			
			// hide
			$a.parent().addClass('hide');
			
			
			// template
			var data = {
					post_id		:	$a.attr('data-post_id'),
					title		:	$a.html(),
					name		:	this.$input.attr('name')
				},
				tmpl = _.template(acf.l10n.data_selector.tmpl_li, data);
			
			
	
			// add new li
			this.$right.find('.data_selector_list').append( tmpl )
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
			
			// validation
			this.$el.closest('.field').removeClass('error');

			
		},
		remove : function( $a ){
			
			// remove
			$a.parent().remove();
			
			
			// show
			this.$left.find('a[data-post_id="' + $a.attr('data-post_id') + '"]').parent('li').removeClass('hide');
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
		}
		
	};
	
	
	/*
	*  acf/setup_fields
	*
	*  run init function on all elements for this field
	*
	*  @type	event
	*  @date	20/07/13
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	$(document).on('acf/setup_fields', function(e, el){
		
		$(el).find('.acf_data_selector').each(function(){
			
			acf.fields.data_selector.set({ $el : $(this) }).init();
			
		});
		
	});
	
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('change', '.acf_data_selector .select-post_type', function(e){
		
		// vars
		var val = $(this).val(),
			$el = $(this).closest('.acf_data_selector');
			
		
		// update attr
	    $el.attr('data-post_type', val);
	    $el.attr('data-paged', 1);
	    
	    
	    // fetch
	    acf.fields.data_selector.set({ $el : $el }).fetch();
		
	});

	
	$(document).on('click', '.acf_data_selector .data_selector_left .data_selector_list a', function( e ){
		
		e.preventDefault();
		
		acf.fields.data_selector.set({ $el : $(this).closest('.acf_data_selector') }).add( $(this) );
		
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf_data_selector .data_selector_right .data_selector_list a', function( e ){
		
		e.preventDefault();
		
		acf.fields.data_selector.set({ $el : $(this).closest('.acf_data_selector') }).remove( $(this) );
		
		$(this).blur();
		
	});
	
	$(document).on('keyup', '.acf_data_selector input.data_selector_search', function( e ){
		
		// vars
		var val = $(this).val(),
			$el = $(this).closest('.acf_data_selector');
			
		
		// update attr
	    $el.attr('data-s', val);
	    $el.attr('data-paged', 1);
	    
	    
	    // fetch
	    clearTimeout( acf.fields.data_selector.timeout );
	    acf.fields.data_selector.timeout = setTimeout(function(){
	    
	    	 acf.fields.data_selector.set({ $el : $el }).fetch();
	    	
	    }, 500);
		
	});
	
	$(document).on('keypress', '.acf_data_selector input.data_selector_search', function( e ){
		
		// don't submit form
		if( e.which == 13 )
		{
			e.preventDefault();
		}
		
	});
	

})(jQuery);