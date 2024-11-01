var Sirat_Demo_Importer_Whizzie = (function($) {

  var t;
  var current_step = '';
  var step_pointer = '';
  var demo_import_type = '';

  // callbacks from form button clicks.
  var callbacks = {
    do_next_step: function(btn) {
      do_next_step(btn);
    },
    install_themes: function(btn) {
      var themes = new ThemeManager();
      themes.init(btn);
    },
    install_plugins: function(btn) {
      var plugins = new PluginManager();
      plugins.init(btn);
    },
    install_widgets: function(btn) {
      demo_import_type = "customize";
      var widgets = new WidgetManager(demo_import_type);
      widgets.init(btn);
    },
    page_builder: function(btn) {
      demo_import_type = "builder";
      var widgets = new WidgetManager(demo_import_type);
      widgets.init(btn);
    },
    install_content: function(btn) {
      var content = new ContentManager();
      content.init(btn);
    }
  };


  function window_loaded() {
    // Get all steps and find the biggest
    // Set all steps to same height
    var maxHeight = 0;

    $('.sirat-demo-importer-whizzie-menu li.sirat-demo-importer-step').each(function(index) {
      $(this).attr('data-height', $(this).innerHeight());
      if ($(this).innerHeight() > maxHeight) {
        maxHeight = $(this).innerHeight();
        console.log(maxHeight);
      }
    });

    $('.sirat-demo-importer-whizzie-menu li .detail').each(function(index) {
      $(this).attr('data-height', $(this).innerHeight());
      $(this).addClass('sirat-demo-importer-scale-down');
    });


    // $('.sirat-demo-importer-whizzie-menu li.sirat-demo-importer-step').css('height', maxHeight);
    $('.sirat-demo-importer-whizzie-menu li.sirat-demo-importer-step').css('height', '100%');
    $('.sirat-demo-importer-whizzie-menu li.sirat-demo-importer-step:first-child').addClass('sirat-demo-importer-active-step');
    $('.sirat-demo-importer-whizzie-nav li:first-child').addClass('sirat-demo-importer-active-step');

    $('.sirat-demo-importer-whizzie-wrap').addClass('sirat-demo-importer-loaded');

    // init button clicks:
    $('.sirat-demo-importer-do-it').on('click', function(e) {
      e.preventDefault();
      step_pointer = $(this).data('step');
      current_step = $('.step-' + $(this).data('step'));
      $('.sirat-demo-importer-whizzie-wrap').addClass('sirat-demo-importer-spinning');
      if ($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined') {
        // we have to process a callback before continue with form submission
        callbacks[$(this).data('callback')](this);
        return false;
      } else {
        loading_content();
        window.location.href = sirat_demo_importer_whizzie_params.admin_url + 'admin.php?page=ibtana-visual-editor-templates';
        return true;
      }
    });

    $('.key-activation-tab-click').on('click', function() {
      document.querySelector('.tab button.tablinks[data-tab="theme_activation"]').click();
    });

    $('.sirat-demo-importer-more-info').on('click', function(e) {
      e.preventDefault();
      var parent = $(this).parent().parent();
      parent.toggleClass('sirat-demo-importer-show-detail');
      if (parent.hasClass('sirat-demo-importer-show-detail')) {
        var detail = parent.find('.detail');
        parent.animate({
            height: parent.data('height') + detail.data('height')
          },
          500,
          function() {
            detail.toggleClass('sirat-demo-importer-scale-down');
          }).css('overflow', 'visible');;
      } else {
        parent.animate({
            height: '100%'
          },
          500,
          function() {
            detail = parent.find('.detail');
            detail.toggleClass('sirat-demo-importer-scale-down');
          }).css('overflow', 'visible');
      }
    });
    $('.sirat-demo-importer-more-info').trigger('click');
    $('.sirat-demo-importer-more-info').hide();
  }

  function loading_content() {

  }


  function do_next_step(btn) {
    current_step.removeClass('sirat-demo-importer-active-step');
    $('.nav-step-' + step_pointer).removeClass('sirat-demo-importer-active-step');
    current_step.addClass('done-step');
    $('.nav-step-' + step_pointer).addClass('done-step');
    current_step.fadeOut(500, function() {
      current_step = current_step.next();
      step_pointer = current_step.data('step');
      current_step.fadeIn();
      current_step.addClass('sirat-demo-importer-active-step');
      $('.nav-step-' + step_pointer).addClass('sirat-demo-importer-active-step');
      $('.sirat-demo-importer-whizzie-wrap').removeClass('sirat-demo-importer-spinning');
    });
  }


  function PluginManager() {

    var complete;
    var items_completed = 0;
    var current_item = '';
    var $current_node;
    var current_item_hash = '';

    console.log('current_item', current_item);

    function ajax_callback(response) {

      console.log('response', response);

      if (typeof response == 'object' && typeof response.message != 'undefined') {
        $current_node.find('span').text(response.message);
        if (typeof response.url != 'undefined') {
          // we have an ajax url action to perform.

          if (response.hash == current_item_hash) {
            $current_node.find('span').text("failed");
            find_next();
          } else {
            current_item_hash = response.hash;

            if ( response.message == "Installing Plugin" ) {
              jQuery.get(response.url, function(response2) {
                process_current();
                $current_node.find('span').text(response.message + sirat_demo_importer_whizzie_params.verify_text);
              }).fail(ajax_callback);
            } else {
              jQuery.post(response.url, response, function(response2) {
                process_current();
                $current_node.find('span').text(response.message + sirat_demo_importer_whizzie_params.verify_text);
              }).fail(ajax_callback);
            }

          }

        } else if (typeof response.done != 'undefined') {
          // finished processing this plugin, move onto next
          find_next();
        } else {
          // error processing this plugin
          find_next();
        }
      } else {
        // error - try again with next plugin
        $current_node.find('span').text("ajax error");
        find_next();
      }
    }

    function process_current() {

      console.log('current_item', current_item);

      if (current_item) {
        // query our ajax handler to get the ajax to send to TGM
        // if we don't get a reply we can assume everything worked and continue onto the next one.
        jQuery.post(sirat_demo_importer_whizzie_params.ajaxurl, {
          action: 'sirat_demo_importer_setup_plugins',
          wpnonce: sirat_demo_importer_whizzie_params.wpnonce,
          slug: current_item
        }, ajax_callback).fail(ajax_callback);
      }
    }

    function find_next() {
      var do_next = false;
      if ($current_node) {
        if (!$current_node.data('done_item')) {
          items_completed++;
          $current_node.data('done_item', 1);
        }
        $current_node.find('.spinner').css('visibility', 'hidden');
      }
      var $li = $('.sirat-demo-importer-whizzie-do-plugins li');
      $li.each(function() {
        if (current_item == '' || do_next) {
          current_item = $(this).data('slug');
          $current_node = $(this);
          process_current();
          do_next = false;
        } else if ($(this).data('slug') == current_item) {
          do_next = true;
        }
      });
      if (items_completed >= $li.length) {
        // finished all plugins!
        complete();
      }
    }


    return {
      init: function(btn) {
        $('.envato-wizard-plugins').addClass('installing');
        complete = function() {
          do_next_step();
          // window.location.href=btn.href;
          // window.location.href = "http://localhost/catapult_themes/whizzie/wp-admin/themes.php?page=whizzie";
        };
        find_next();
      }
    }

  }

  function WidgetManager(demo_type) {
    $('.step-loading').css('display', 'block');
    var demo_action = '';
    if (demo_type == 'builder') {
      jQuery('.finish-buttons .wz-btn-customizer').css('display', 'none');
      jQuery('.finish-buttons .wz-btn-builder').css('display', 'inline-block');

      function import_widgets() {
        jQuery.post(
          sirat_demo_importer_whizzie_params.ajaxurl, {
            action: 'setup_builder',
            wpnonce: sirat_demo_importer_whizzie_params.wpnonce
          }, ajax_callback).fail(ajax_callback);
      }
      $('.nav-step-done').attr('data-enable', 1);
    } else {
      jQuery('.finish-buttons .wz-btn-customizer').css('display', 'inline-block');
      jQuery('.finish-buttons .wz-btn-builder').css('display', 'none');

      function import_widgets() {
        jQuery.post(
          sirat_demo_importer_whizzie_params.ajaxurl, {
            action: 'setup_widgets',
            wpnonce: sirat_demo_importer_whizzie_params.wpnonce
          }, ajax_callback_customizer).fail(ajax_callback_customizer);
      }
      $('.nav-step-done').attr('data-enable', 1);
    }
    return {
      init: function(btn) {
        ajax_callback = function(response) {
          var obj = JSON.parse(response);
          if (obj.home_page_url != "") {
            jQuery('.wz-btn-builder').attr('href', obj.home_page_url);
          }
          do_next_step();
        }
        ajax_callback_customizer = function() {
          do_next_step();
        }

        import_widgets();
      }
    }
  }


  function ThemeManager() {

    var complete;
    var items_completed = 0;
    var current_item = '';
    var $current_node;
    var current_item_hash = '';

    function ajax_callback(response) {

      if (typeof response == 'object' && typeof response.message != 'undefined') {
        $current_node.find('span').text(response.message);
        if (typeof response.url != 'undefined') {
          // we have an ajax url action to perform.

          if (response.hash == current_item_hash) {
            $current_node.find('span').text("failed");
            find_next();
          } else {
            current_item_hash = response.hash;
            jQuery.get(response.url, function(response2) {
              process_current();
              $current_node.find('span').text(response.message + sirat_demo_importer_whizzie_params.verify_text);
            }).fail(ajax_callback);
          }

        } else if (typeof response.done != 'undefined') {
          // finished processing this plugin, move onto next
          find_next();
        } else {
          // error processing this plugin
          find_next();
        }
      } else {
        // error - try again with next plugin
        $current_node.find('span').text("ajax error");
        find_next();
      }
    }


    function process_current() {
      if (current_item) {
        // query our ajax handler to get the ajax to send to TGM
        // if we don't get a reply we can assume everything worked and continue onto the next one.
        jQuery.post(sirat_demo_importer_whizzie_params.ajaxurl, {
          action: 'sirat_demo_importer_setup_themes',
          wpnonce: sirat_demo_importer_whizzie_params.wpnonce,
          slug: current_item
        }, ajax_callback).fail(ajax_callback);

      }
    }

    function find_next() {
      var do_next = false;
      if ($current_node) {
        if (!$current_node.data('done_item')) {
          items_completed++;
          $current_node.data('done_item', 1);
        }
        $current_node.find('.spinner').css('visibility', 'hidden');
      }
      var $li = $('.sirat-demo-importer-do-themes li');
      $li.each(function() {
        if (current_item == '' || do_next) {
          current_item = $(this).data('slug');
          $current_node = $(this);
          process_current();
          do_next = false;
        } else if ($(this).data('slug') == current_item) {
          do_next = true;
        }
      });
      if (items_completed >= $li.length) {
        // finished all themes!
        complete();
      }
    }

    return {
      init: function(btn) {
        $('.envato-wizard-plugins').addClass('installing');
        complete = function() {
          do_next_step();
        };
        find_next();
      }
    }

  }


  function ContentManager() {

    var complete;
    var items_completed = 0;
    var current_item = '';
    var $current_node;
    var current_item_hash = '';

    function ajax_callback(response) {
      if (typeof response == 'object' && typeof response.message != 'undefined') {
        $current_node.find('span').text(response.message);
        if (typeof response.url != 'undefined') {
          // we have an ajax url action to perform.
          if (response.hash == current_item_hash) {
            $current_node.find('span').text("failed");
            find_next();
          } else {
            current_item_hash = response.hash;
            jQuery.post(response.url, response, ajax_callback).fail(ajax_callback); // recuurrssionnnnn
          }
        } else if (typeof response.done != 'undefined') {
          // finished processing this plugin, move onto next
          find_next();
        } else {
          // error processing this plugin
          find_next();
        }
      } else {
        // error - try again with next plugin
        $current_node.find('span').text("ajax error");
        find_next();
      }
    }

    function process_current() {
      if (current_item) {

        var $check = $current_node.find('input:checkbox');
        if ($check.is(':checked')) {
          // process htis one!
          jQuery.post(sirat_demo_importer_whizzie_params.ajaxurl, {
            action: 'envato_setup_content',
            wpnonce: sirat_demo_importer_whizzie_params.wpnonce,
            content: current_item
          }, ajax_callback).fail(ajax_callback);
        } else {
          $current_node.find('span').text("Skipping");
          setTimeout(find_next, 300);
        }
      }
    }


    return {
      init: function(btn) {
        $('.envato-setup-pages').addClass('installing');
        $('.envato-setup-pages').find('input').prop("disabled", true);
        complete = function() {
          loading_content();
          window.location.href = btn.href;
        };
        find_next();
      }
    }
  }

  return {
    init: function() {
      t = this;
      $(window_loaded);
    },
    callback: function(func) {
      console.log(func);
      console.log(this);
    }
  }

})(jQuery);

Sirat_Demo_Importer_Whizzie.init();

jQuery(document).ready(function() {

  jQuery('.sirat-demo-importer-setup-finish .sirat-demo-importer-finish-btn a').click(function() {
    jQuery('.tab-sec button.tablinks:nth-child(2)').addClass('active');
  });






});
