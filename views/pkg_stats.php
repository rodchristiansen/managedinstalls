<?php $this->view('partials/head', ['scripts' => ['clients/client_list.js']]); ?>

<div class="container">
  <div class="row">
    <div class="col-lg-12">
      <h3>
        <span data-i18n="managedinstalls.installratio_report"></span>
        <span id="total-count" class='label label-primary'>…</span>
      </h3>

      <table id="pkg-stats-table" class="table table-striped">
        <thead>
          <tr>
            <th data-i18n="name"></th>
            <th data-i18n="version"></th>
            <th data-i18n="displayname"></th>
            <th data-i18n="ratio"></th>
            <th data-i18n="installed"></th>
            <th data-i18n="pending"></th>
            <th data-i18n="failed"></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(document).on('appReady', function(e, lang) {
    $.getJSON(appUrl + '/module/managedinstalls/get_pkg_stats/', function(data){
        var dataSet = [],
            linkUrl = appUrl + '/module/managedinstalls/listing/';

        $.each(data, function(index, val){
            if(val.name){
                var installed = +val.installed || 0,
                    pending = +val.pending_install || 0,
                    failed = +val.install_failed || 0,
                    total = installed + pending + failed,
                    pct = total ? Math.round((installed / total) * 100) : 0;

                dataSet.push([
                    val.name,
                    val.version || '',
                    val.display_name || val.name,
                    pct + '%',
                    installed,
                    pending,
                    failed
                ]);
            }
        });

        $('#pkg-stats-table').dataTable({
            data: dataSet,
            serverSide: false,
            order: [0, 'asc'],
            createdRow: function(nRow, aData, iDataIndex) {
                $('td:eq(0)', nRow).html('<a href="'+linkUrl+aData[0]+'">'+aData[0]+'</a>');
                $('td:eq(1)', nRow).html('<a href="'+linkUrl+aData[0]+'/'+aData[1]+'">'+aData[1]+'</a>');
            },
            drawCallback: function(oSettings) {
                $('#total-count').html(oSettings.fnRecordsTotal());
            }
        });
    });
});
</script>

<?php $this->view('partials/foot'); ?>
