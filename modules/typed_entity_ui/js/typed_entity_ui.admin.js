/**
 * @file
 * JSON:API Extras resources behaviors.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * Filters the resources tables by a text input search string.
   */
  Drupal.behaviors.resourcesTableFilterByText = {
    attach: function (context) {
      const $input = $('input.typed-repositories-filter-text', context).once(
        'typed-repositories-filter-text',
      );
      const $table = $($input.attr('data-table'));
      let $rows;

      function filterViewList(e) {
        const query = $(e.target).val().toLowerCase();

        function showViewRow(index, row) {
          const $row = $(row);
          $row
            .closest('tr')
            .toggle(
              $('.plugin-id', $row).is(
                ":contains('" + query.toLowerCase() + "')",
              ),
            );
        }

        // Filter if the length of the query is at least 2 characters.
        if (query.length >= 2) {
          $rows.each(showViewRow);
        } else {
          $rows.show();
        }
      }

      if ($table.length) {
        $rows = $table.find('tbody tr');
        $input.on('keyup', filterViewList);
      }
    },
  };

  $.expr[':'].contains = $.expr.createPseudo(function (arg) {
    return function (elem) {
      return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
  });
})(jQuery, Drupal);
