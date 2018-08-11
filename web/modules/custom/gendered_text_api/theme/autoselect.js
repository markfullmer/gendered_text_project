// /**
//  * @file
//  * Placeholder file for custom sub-theme behaviors.
//  *
//  */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.subthemeBehavior = {
    attach: function (context, settings) {

      $('#male').click(function() {
        male();
      });

      $('#female').click(function() {
        female();
      });

      $('#randomize').click(function() {
        randomize();
      });

      function randomize() {
        var selects = document.getElementsByTagName('select');
        for(var i=0; i<selects.length; i++){
          var items = selects[i].getElementsByTagName('option');
          var index = Math.floor(Math.random() * items.length);
          selects[i].selectedIndex = index;
        }
      }     

      function female() {
        var selects = document.getElementsByTagName('select');
        for(var i=0; i<selects.length; i++){
          var items = selects[i].getElementsByTagName('option');
          var index = 0;
          selects[i].selectedIndex = index;
        }
      }     

      function male() {
        var selects = document.getElementsByTagName('select');
        for(var i=0; i<selects.length; i++){
          var items = selects[i].getElementsByTagName('option');
          var index = 1;
          selects[i].selectedIndex = index;
        }
      }

    }
  };

})(jQuery, Drupal);