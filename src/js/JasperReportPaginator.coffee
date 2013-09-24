class this.JasperReportPaginator
	constructor: () ->
		@report = $('#jasper_report')
		@pages = @report.find('.jrPage')
		@pagination = $('#jasper_report_container .pagination')
		@current_page = 0
		@max_pages = @pages.length
	show_pagination: () ->
		@pagination.show()
		@show_current_page()
	disable: () ->
		@pagination.find('.next, .prev').addClass('disabled')
		@pages.show()
	show_current_page: () ->
		if @has_next()
			@pagination.find('.next').removeClass('disabled')
		else
			@pagination.find('.next').addClass('disabled')
		
		if @has_prev()
			@pagination.find('.prev').removeClass('disabled')
		else
			@pagination.find('.prev').addClass('disabled')
		
		@pages.hide().eq(@current_page).show()
	next: () ->
		return true unless @has_next()
		@current_page = @current_page + 1
		@show_current_page()
	has_next: () ->
		@current_page < (@max_pages - 1)
	prev: () ->
		return true unless @has_prev()
		@current_page = @current_page - 1
		@show_current_page()
	has_prev: () ->
		@current_page > 0
