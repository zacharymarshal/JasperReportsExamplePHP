#=require /lib/JasperReportPaginator/js/JasperReportPaginator.js.coffee
$ ->
  $("#jasper_report table td > br").remove()
  paginator = new JasperReportPaginator()
  paginator.show_pagination()
  $('.jasper_report_next').click ->
    paginator.next()
  $('.jasper_report_prev').click ->
    paginator.prev()
  $('.jasper_report_disable_pagination').click ->
    $(@).parent().addClass('disabled')
    paginator.disable()