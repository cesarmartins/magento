
/*
 * Extends varienGrid (js/grid.js) with a a method to submit stock data
 */
varienGrid.prototype.updateStock = function(url) {
    this.reloadParams = this.getGridData();
    this.reload(url);
    this.reloadParams = false;
};

varienGrid.prototype.getGridData = function() {
    var data = {};
    $$('#stock_grid_table tbody tr').each(function(tr,index) {
        tr = $(tr);
        var id = 1 * tr.down('td.id').innerHTML;
        data['data['+id+'][item_id]'] = tr.down('td.item_id').innerHTML.strip();
        data['data['+id+'][qty]'] = 1 * tr.down('td.qty').down('input').value;
        data['data['+id+'][is_in_stock]'] = 1 * tr.down('td.is_in_stock').down('input').checked;
    });
    return data;
};

