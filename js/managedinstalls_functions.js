// Global object to hold variables
var managedInstallsVariables = {
    pkgName: '',
    pkgVersion: '',
    clientType: ''
};

// Initialize managed installs view
var initializeManagedInstalls = function(pkgName, pkgVersion, clientType){
    managedInstallsVariables.pkgName = decodeURIComponent(pkgName);
    managedInstallsVariables.pkgVersion = decodeURIComponent(pkgVersion);
    managedInstallsVariables.clientType = decodeURIComponent(clientType || '');

    let headingText = managedInstallsVariables.pkgName;
    if(pkgVersion) headingText += ' (' + managedInstallsVariables.pkgVersion + ')';

    $('h3>span:first').text(headingText);
};

// Filter function
var managedInstallsFilter = function(colNumber, d){
    d.where = [];

    if(managedInstallsVariables.pkgName){
        d.where.push({
            table: 'managedinstalls',
            column: 'name',
            value: managedInstallsVariables.pkgName
        });

        if(managedInstallsVariables.pkgVersion){
            d.where.push({
                table: 'managedinstalls',
                column: 'version',
                value: managedInstallsVariables.pkgVersion
            });
        }
    }

    if(managedInstallsVariables.clientType){
        d.where.push({
            table: 'managedinstalls',
            column: 'client_type',
            value: managedInstallsVariables.clientType
        });
    }
};

// Formatter function for install status
var managedInstallStatus = function(colNumber, row){
    var col = $('td:eq(' + colNumber + ')', row),
        status = col.text();

    if(mr.statusFormat[status]){
        status = '<span class="label label-' + mr.statusFormat[status].type + '">' + status + '</span>';
    }
    col.html(status);
};
