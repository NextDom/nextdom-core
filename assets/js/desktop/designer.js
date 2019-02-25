$('document').ready(function() {
    
	function cleanup() {
		var dwnld_html = $($('#designer-html-container').html());
		dwnld_html.find('.close-grid').remove();
		dwnld_html.find('.control-label').remove();
		var clsElements = dwnld_html.find("*");
		$(clsElements).each(function() {
		    $(this).removeClass('sortable').removeClass('column').removeClass('ui-sortable').removeClass('draggable').removeClass('box').removeClass('preview').removeClass('ui-draggable').removeAttr('data-original-title').removeAttr('title');
		    if ($(this).attr('class') == "") {
		        $(this).removeAttr('class');
		    }
		});
		var cmpHtml = "";
		dwnld_html.each(function() {
		    cmpHtml += $(this).html();
		});
		return cmpHtml;
	}

	function saveAttributes(that) {
		var field = $(that.parents().find('.arrow')[0]).parent().prev();
		var field_options_textarea = that.closest('form').find('#options-textarea').val();
		var field_label = that.closest('form').find('#label').val();
		var button_text = that.closest('form').find('#btn-text').val();
		var button_color_css = that.closest('form').find('#btn-color-css').val();
		var field_css = that.closest('form').find('#inputsize').val();
		var field_placeholder = that.closest('form').find('#placeholder').val();
		var field_id = that.closest('form').find('#id').val();
		var field_style = that.closest('form').find('#field-style').val();
		var field_multiselect = that.closest('form').find('#multiple-select').is(":checked");
		$(field.children()[1]).attr('id', field_id).attr('class', field_css).attr('placeholder', field_placeholder);
		$('.draggable').popover('hide');
		$(field.children()[0]).text(field_label);
		if (field.attr('id') === 'radio') {
		    if (typeof (field_options_textarea) !== "undefined" && field_options_textarea.length > 0) {
		        var opts = field_options_textarea.split('\n');
		        var options_html = $('<div></div>');
		        for (i = 0; i < opts.length; i++) {
		            var opts_html = $('<label class="radio" style="display:block;"></label>');
		            opts_html.text(opts[i]);
		            opts_html.append('<input type="radio" name="radios" value="">');
		            opts_html.children('input').val(opts[i]);
		            options_html.append(opts_html);
		        }
		        field.children().each(function() {
		            if ($(this).hasClass('controls')) {
		                $(this).html(options_html.html());
		            }
		        });
		    }
		}
		if (field.attr('id') === 'checkbox') {
		    if (typeof (field_options_textarea) !== "undefined" && field_options_textarea.length > 0) {
		        var opts = field_options_textarea.split('\n');
		        var options_html = $('<div></div>');
		        for (i = 0; i < opts.length; i++) {
		            var opts_html = $('<label class="checkbox" style="display:block;"></label>');
		            opts_html.text(opts[i]);
		            opts_html.append('<input type="checkbox" name="checkboxs" value="">');
		            opts_html.children('input').val(opts[i]);
		            options_html.append(opts_html);
		        }
		        field.children().each(function() {
		            if ($(this).hasClass('controls')) {
		                $(this).html(options_html.html());
		            }
		        });
		    }
		}
		if (field.attr('id') === 'select-list') {
		    if (typeof (field_options_textarea) !== "undefined" && field_options_textarea.length > 0) {
		        var opts = field_options_textarea.split('\n');
		        var options_html = $('<div></div>');
		        for (i = 0; i < opts.length; i++) {
		            var opts_html = $('<option></option>');
		            opts_html.text(opts[i]);
		            options_html.append(opts_html);
		        }
		        field.find('select').html(options_html.html());
		    }
		    if (field_multiselect) {
		        field.find('select').attr('multiple', 'multiple');
		    }
		    else {
		        field.find('select').removeAttr('multiple');
		    }
		}
		field.children().each(function() {
		    if ($(this).hasClass('controls')) {
		        $(this).children().each(function() {
		            $(this).attr('style', field_style);
		        });
		    }
		    $(this).attr('style', field_style);
		});
		if (button_text) {
		    $(field.children()[1]).text(button_text);
		}
		if (button_color_css) {
		    $(field.children()[1]).addClass(button_color_css);
		    $(field.children()[1]).addClass('btn');
		}
	}

	function makeDraggable() {
		$(".sortable").sortable({opacity: .35, connectWith: ".column"});
		$(".draggable").draggable({
		    helper: 'clone',
		    opacity: .35,
		    start: function() {
		        dragged_clone = null;
		    },
		    stop: function(e, t) {
		        if ($(this).draggable('widget').attr('id') === 'grid') {
		            var grid = gridSystemGenerator($($(this).draggable('widget').children()[1]));
		            if (grid) {
		                grid.appendTo($('.sortable').not('.column'));
		                grid.find('.sortable').each(function() {
		                    $(this).sortable({opacity: .35, connectWith: ".column"});
		                });
		                $('.sortable').delegate('.close-grid', 'click', function() {
		                    $(this).next().remove();
		                    $(this).remove();
		                });
		            }
		        }
		        else if ($('#html-container').children().length > 0) {
		            dragged_clone = $(this).draggable('widget').clone();
		            dragged_clone.popover({
		                html: true,
		                content: function() {
		                    return $("#popover-" + $(this).attr('id')).html();
		                }
		            });
		            $('.sortable.column').on('mouseover', function(event) {
		                if (dragged_clone) {
		                    dragged_clone.appendTo($(this));
		                }
		                dragged_clone = null;
		            });
		            $('.sortable.column').delegate('button#saveattr', 'click', function(e) {
		                e.preventDefault();
		                saveAttributes($(this));
		            });
		            $('.sortable.column').delegate('button#cancel', 'click', function(e) {
		                e.preventDefault();
		                $('.draggable').popover('hide');
		            });
		            $('.sortable.column').delegate('button#remove', 'click', function(e) {
		                e.preventDefault();
		                var field = $($(this).parents().find('.arrow')[0]).parent().prev();
		                $('.draggable').popover('hide');
		                field.remove();
		                e.stopPropagation();
		                e.stopImmediatePropagation();
		            });
		        }
		        else {
		            alert('Elements can only be dragged on grids, Please drag a grid first !');
		        }
		    }
		});
		function gridSystemGenerator(details) {
		    var e = 0;
		    var t = '<div><a class="remove label label-important close-grid"><i class="icon-remove icon-white"></i>Remove</a><div class="row-fluid clearfix">';
		    var n = details.val().split(" ", 12);
		    $.each(n, function(n, r) {
		        if (!isNaN(parseInt(r))) {
		            e = e + parseInt(r);
		            t += '<div class="span' + r + ' column sortable"></div>';
		        }
		    });
		    t += '</div></div>';
		    if (e == 12) {
		        return $(t);
		    }
		    else
		    {
		        alert("Total grid column size must be equal to 12");
		        return false;
		    }

		}
    	}
    $.get("/assets/js/desktop/designer/config.json", function(response) {
		var components = response.components;
		var i = 0;
		var j = -1;
		var load_file = function(url) {
			$.get(url, function(res) {

			if (url.indexOf('attributesForm') > -1) {
				$('.top-container').append(res);
			}
			else {
				$('.elements').append(res);
			}
			if (j === components.length - 1) {
				makeDraggable();
			}
			if (i >= components.length - 1) {

				if (typeof (components[++j]) !== "undefined") {
				load_file("/assets/designer/attributesForm/" + components[j] + ".html");
				}
			}
			if (typeof (components[++i]) !== "undefined") {
				load_file("/assets/designer/elements/" + components[i] + ".html");
			}

	    });
	};
	load_file("/assets/designer/elements/" + components[i] + ".html");
	var dragged_clone = null;

    });
    $('#designer-download-html').click(function() {
	var cmpHtml = cleanup();
	var blob = new Blob([cmpHtml], {type: "text/plain;charset=utf-8"});
	saveAs(blob, "output.html");
    });
    $('#designer-preview-html').on('click', function() {
	$('#designer-modal-body').html(cleanup());
    });
});
