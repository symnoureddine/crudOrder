/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';



const $ = require('jquery');
window.$ = $;
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap');

//import dt from 'datatables.net-bs4';
//dt(window, $);
//require('datatables.net-bs4')($);


// Import datatables and the required plugins, using the bootstrap 4 styling
import 'datatables.net-bs4';
import 'datatables.net-select-bs4';

$(function () {

    /*  $('.mydatatable tfoot th').each(function () {
          var title = $(this).text();
          $(this).html('<input type="text" placeholder="Chercher ' + title + '" />');
      });*/

    // DataTable
    $('.mydatatable').DataTable({
        /*  initComplete: function () {
              // Apply the search
              this.api().columns().every(function () {
                  var that = this;
  
                  $('input', this.footer()).on('keyup change clear', function () {
                      if (that.search() !== this.value) {
                          that
                              .search(this.value)
                              .draw();
                      }
                  });
              });
          }*/
    });

});