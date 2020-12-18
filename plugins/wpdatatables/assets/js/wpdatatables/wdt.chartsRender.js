(function($){
    $(window).on('load', function(){

        var wdtGoogleCharts = [];

        if (typeof wpDataCharts !== 'undefined') {

            for( var chart_id in wpDataCharts ){

                if( wpDataCharts[chart_id].engine == 'google' ){
                    var wdtChart = new wpDataTablesGoogleChart();
                    wdtChart.setType( wpDataCharts[chart_id].render_data.type );
                    wdtChart.setColumns( wpDataCharts[chart_id].render_data.columns );
                    wdtChart.setRows( wpDataCharts[chart_id].render_data.rows );
                    wdtChart.setOptions( wpDataCharts[chart_id].render_data.options );
                    wdtChart.setGrouping( wpDataCharts[chart_id].group_chart );
                    wdtChart.setContainer( wpDataCharts[chart_id].container );
                    wdtChart.setColumnIndexes( wpDataCharts[chart_id].render_data.column_indexes );
                    if( typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[chart_id] !== 'undefined' ){
                        wdtChart.setRenderCallback( wpDataChartsCallbacks[chart_id] );
                    }
                    wdtGoogleCharts.push( wdtChart );
                } else if ( wpDataCharts[chart_id].engine == 'highcharts' ) {
                    var wdtChart = new wpDataTablesHighchart();
                    wdtChart.setOptions( wpDataCharts[chart_id].render_data.options );
                    wdtChart.setMultiplyYaxis( wpDataCharts[chart_id].render_data );
                    wdtChart.setType( wpDataCharts[chart_id].render_data.type );
                    wdtChart.setWidth( wpDataCharts[chart_id].render_data.width );
                    wdtChart.setHeight( wpDataCharts[chart_id].render_data.height );
                    wdtChart.setColumnIndexes( wpDataCharts[chart_id].render_data.column_indexes );
                    wdtChart.setGrouping( wpDataCharts[chart_id].group_chart );
                    wdtChart.setContainer( '#'+wpDataCharts[chart_id].container );
                    wdtChart.setNumberFormat( wpDataCharts[chart_id].render_data.wdtNumberFormat );
                    if( typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[chart_id] !== 'undefined' ){
                        wdtChart.setRenderCallback( wpDataChartsCallbacks[chart_id] );
                    }
                    if( wpDataCharts[chart_id].follow_filtering != 1 ) {
                        wdtChart.render();
                    }
                } else if ( wpDataCharts[chart_id].engine == 'chartjs' ) {
                    var wdtChart = new wpDataTablesChartJS();
                    wdtChart.setData( wpDataCharts[chart_id].render_data.options.data );
                    wdtChart.setOptions( wpDataCharts[chart_id].render_data.options.options );
                    wdtChart.setGlobalOptions( wpDataCharts[chart_id].render_data.options.globalOptions );
                    wdtChart.setType( wpDataCharts[chart_id].render_data.configurations.type );
                    wdtChart.setColumnIndexes( wpDataCharts[chart_id].render_data.column_indexes );
                    wdtChart.setGrouping( wpDataCharts[chart_id].group_chart );
                    wdtChart.setContainer( document.getElementById("chartJSContainer_" + chart_id));
                    wdtChart.setCanvas( document.getElementById("chartJSCanvas_" + chart_id));
                    wdtChart.setContainerOptions( wpDataCharts[chart_id].render_data.configurations );
                    if( typeof wpDataChartsCallbacks !== 'undefined' && typeof wpDataChartsCallbacks[chart_id] !== 'undefined' ){
                        wdtChart.setRenderCallback( wpDataChartsCallbacks[chart_id] );
                    }
                    if( wpDataCharts[chart_id].follow_filtering != 1 ) {
                        wdtChart.render();
                    }
                }

                if( wpDataCharts[chart_id].follow_filtering == 1 ){
                    // Find the wpDataTable object
                    var $wdtable = $('table.wpDataTable[data-wpdatatable_id='+wpDataCharts[chart_id].wpdatatable_id+']');
                    if( $wdtable.length > 0 ){
                        var wdtObj = wpDataTables[$wdtable.get(0).id];
                        wdtChart.setConnectedWPDataTable( wdtObj );
                        wdtChart.enableFollowFiltering();
                        wdtObj.fnDraw();
                    }else{
                        wdtChart.render();
                    }
                }
            }
        }

        // Setting the callback for rendering Google Charts
        if( wdtGoogleCharts.length ){
            var wdtGoogleRenderCallback = function(){
                for( var i in wdtGoogleCharts ){
                    wdtGoogleCharts[i].render();
                }
            }
            google.charts.setOnLoadCallback( wdtGoogleRenderCallback );
        }

    })

})(jQuery);
