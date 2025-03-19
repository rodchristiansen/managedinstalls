<div class="col-lg-4 col-md-6">
	<div class="card" id="pending-munki-widget">
		<div class="card-header" data-container="body" data-i18n="[title]managedinstalls.widget.pending_munki.tooltip">
			<i class="fa fa-shopping-cart"></i>
            <span data-i18n="managedinstalls.widget.pending_munki.title"></span>
            <a href="/module/managedinstalls/listing/#pending_install" class="pull-right"><i class="fa fa-list"></i></a>
		</div>
		<div class="list-group scroll-box"></div>
	</div>
</div>

<script>
$(document).on('appUpdate', function(e, lang) {
	var box = $('#pending-munki-widget div.scroll-box').empty();
	$.getJSON(appUrl + '/module/managedinstalls/get_pending_installs/munki', function(data) {
		if(data.length){
			$.each(data, function(i,d){
				var badge = '<span class="badge pull-right">'+d.count+'</span>',
                    url = appUrl+'/module/managedinstalls/listing/'+d.name+'#pending_install',
					display_name = d.display_name || d.name;
				box.append('<a href="'+url+'" class="list-group-item">'+display_name+' '+d.version+badge+'</a>');
			});
		} else {
			box.append('<span class="list-group-item">'+i18n.t('managedinstalls.no_updates_pending')+'</span>');
		}
	});
});
</script>
