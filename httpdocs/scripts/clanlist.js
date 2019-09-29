var gridDiv = document.querySelector('#myGrid');

var columnDefs = [
    {headername: 'Rang', lockPosition: true, valueGetter: 'node.rowIndex * 1 + 1', cellClass: 'locked-col', width: 120, suppressNavigable: true, sortable: false,filter: false},
    {
        headerName: 'Clan Infos',
        children: [
            {headerName: 'Name', field: 'Name'},
            {headerName: 'Tag', field: 'tag'},
            {headerName: 'zuletzt aktiv', field: 'active', columnGroupShow: 'open', valueFormatter: dateFormatter},
            {headerName: 'aktualisiert', field: 'updated', columnGroupShow: 'open', valueFormatter: dateFormatter},
            {headerName: 'hinzugefügt', field: 'added', columnGroupShow: 'open', valueFormatter: dateFormatter},
        ]
    },
    {
        headerName: 'Spiel Anzahl',
        children: [
            {headerName: 'Spiele', field: 'games', comparator: numComparator,sort: 'desc'},
            {headerName: 'Bac', field: 'Bac', comparator: numComparator},
            {headerName: 'Bac-Rate', columnGroupShow: 'open', field: 'Bac-Rate', comparator: numComparator,
                valueGetter: function(params) {
                    return ((params.data.Bac / params.data.games) * 100).toFixed(0);
                },
                valueFormatter: percentFormatter,
                cellClassRules: {
                    'blue': 'x > 95',
                    'green': 'x > 80 && x < 96',
                    'orange': 'x > 50 && x < 81',
                    'red': 'x => 0 && x < 51',
                }
            }
        ]
    },
    {
        headerName: 'Gewinne & Verluste',
        children: [
            {headerName: 'Siege', field: 'Wins', comparator: numComparator},
            {headerName: "Verluste", columnGroupShow: 'open', colId: 'loses',
                valueGetter: function(params) {
                    return params.data.games - params.data.Wins;
                }},
            {headerName: "Winrate", colId: 'loses',
                valueGetter: function(params) {
                    return ((params.data.Wins / params.data.games) * 100).toFixed(0);
                },
                valueFormatter: percentFormatter,
                cellClassRules: {
                    'blue': 'x > 80',
                    'green': 'x > 49 && x < 81',
                    'orange': 'x > 25 && x < 50',
                    'red': 'x < 26',
                }
            },
        ]
    },
    {
        headerName: 'Andere Infos',
        children: [
            {headerName: 'Eloveränderung', field: 'Elo', comparator: numComparator,
                cellClassRules: {
                    'blue': 'x > 450',
                    'green': 'x > 0 && x < 451',
                    'orange': 'x > -100 && x < 1',
                    'red': 'x < -101',
                }
                },
            {headerName: 'Spielzeit', field: 'time', comparator: numComparator,valueFormatter: timeFormatter},
            {headerName: 'Dathmatches', field: 'dms', comparator: numComparator},
        ]
    }
];

function percentFormatter(params) {
    return params.value + "%";
}

function dateFormatter(params) {
    var date = new Date(params.value);
    var now = new Date();

    var timeDiffUpdated = Math.abs(now.getTime() - date.getTime()) / 1000;
    return timeCalculator(timeDiffUpdated);
}

function timeFormatter(params) {
    return timeCalculator(params.value * 60);
}

var gridOptions = {
    animateRows: true,
    columnDefs: columnDefs,
    groupSelectsChildren: false,
    rowSelection: 'multiple',
    copyHeadersToClipboard: true,
    suppressMenuHide: true,
    suppressMultiSort: false,
    multiSortKey: 'ctrl',
    floatingFilter: true,
    onSortChanged: function(){gridOptions.api.refreshCells();},
    onFilterChanged: function(){gridOptions.api.refreshCells();},
    suppressHorizontalScroll: false,
    defaultColDef: {
        sortable: true,
        cellClass: 'text-cell',
        headerClass: 'text-header',
        resizable: true,
        filter: 'agTextColumnFilter',
        filterParams: 'getStandardFilter'
    }
};
console.log(document.body.clientWidth);
if (document.body.clientWidth >= 992) {
    var grid = new agGrid.Grid(gridDiv, gridOptions);
    grid.gridOptions.api.sizeColumnsToFit();
    window.onresize = function (event) {
        grid.gridOptions.api.sizeColumnsToFit();
    };
    jsonLoad(function (data) {
        gridOptions.api.setRowData(data);
    });
} else {
    gridDiv.innerHTML = "<div class='center-me'><h2 class='text-danger'>Diese Seite ist nicht mobile optimiert</h2><br><h3><a href='/'>Home</a></h3></div>";
}



function numComparator(amount1, amount2) {

    if (amount1 === undefined && amount2 === undefined) {
        return 0;
    }
    if (amount1 === undefined) {
        return -1;
    }
    if (amount2 === undefined) {
        return 1;
    }

    amount1 = parseInt(amount1);
    amount2 = parseInt(amount2);

    return amount1 - amount2;
}


function getStandardFilter() {
    return filter = {
        filterOptions: ['contains', 'notContains'],
        debounceMs: 0,
        caseSensitive: false,
        suppressAndOrCondition: true
    }
}

function jsonLoad(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/api.php?getAllClans'); // by default async
    xhr.responseType = 'json'; // in which format you expect the response to be

    xhr.onload = function () {
        if (this.status == 200) {// onload called even on 404 etc so check the status
            callback(this.response);
        }
    };

    xhr.onerror = function () {
        console.log('loading data error');
    };

    xhr.send();
}


function timeCalculator(seconds) {
    seconds = Math.floor(seconds);
    if (seconds < 60) {
        return seconds + " s";
    }

    var min = Math.floor(seconds / 60);
    if (min < 60) {
        return min + " min";
    }

    var hours = Math.floor(min / 60);
    if (hours < 24) {
        return hours + " h";
    }

    var days = Math.floor(hours / 24);
    if (days < 7) {
        if( days > 1) {
            return days + " Tage";
        }
        return days + " Tag";
    }

    var weeks = Math.floor(days / 7);
    if (days < 30) {
        if(weeks > 1) {
            return weeks + " Wochen";
        }
        return weeks + " Woche";
    }

    var months = Math.floor(days / 30);
    if(months > 1) {
        return months +  " Monate";
    }
    return months +  " Monat";
}