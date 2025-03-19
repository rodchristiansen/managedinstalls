<div class="col-lg-4 col-md-6">
    <div class="card" id="pending-widget">
        <div class="card-header">
            <i class="fa fa-moon-o"></i>
            <span data-i18n="managedinstalls.widget.pending.title"></span>
            <a href="/module/managedinstalls/listing/#pending_install" class="pull-right"><i class="fa fa-list"></i></a>
        </div>
        <div class="list-group scroll-box"></div>
    </div>
</div>

<script>
$(document).on('appUpdate', function(e, lang) {
	var box = $('#pending-widget div.scroll-box');
	$.getJSON(appUrl + '/module/managedinstalls/get_clients/pending_install/24', function(data) {
		box.empty();
		if(data.length){
			$.each(data, function(i,d){
				var badge = '<span class="badge pull-right">'+d.count+'</span>',
                    url = appUrl+'/clients/detail/'+d.serial_number+'#tab_munki';
				box.append('<a href="'+url+'" class="list-group-item">'+(d.computer_name || i18n.t('empty'))+badge+'</a>');
			});
		} else {
			box.append('<span class="list-group-item">'+i18n.t('no_clients')+'</span>');
		}
	});
});
</script>
