(function($) {

  function get_client_ibtana_elementor_templates() {
    jQuery.ajax({
      method: "POST",
      url: sirat_wizard_script_params.SIRAT_DEMO_IMPORTER_LICENCE_ENDPOINT + "get_client_ibtana_elementor_templates",
      data: JSON.stringify(
        {
            "limit": 9,
            "start": 1,
            "search": ""
        }
      ),
      dataType: 'json',
      contentType: 'application/json',
    }).done(function( data ) {

      if ( data.success === true ) {

        var elementor_templates = data.data.elementor_templates;

        for ( var i = 0; i < elementor_templates.length; i++ ) {

          var elementor_template = elementor_templates[i];

          var pro_badge = '';
          if ( elementor_template.is_paid == "1" ) {
            pro_badge = sirat_wizard_script_params.pro_badge;
          }

          jQuery('.theme-browser.content-filterable .themes.wp-clearfix').append(
            `<div class="theme" data-slug="` + elementor_template.slug + `">
              <div class="theme-screenshot">
                <object type="image/svg+xml" data="${pro_badge}"></object>
                <img src="` + elementor_template.image + `" alt="">
              </div>
              <span class="more-details" data-description="` + elementor_template.description + `" data-iframe="` + elementor_template.demo_url + `" data-is-paid="`+elementor_template.is_paid+`">
                Details &amp; Preview
              </span>
              <div class="theme-id-container">
                <h3 class="theme-name">` + elementor_template.title + `</h3>
              </div>
            </div>`
          );

        }

        $( '#elementor_templates' ).removeClass( 'sirat-demo-importer-spinning' );
      }

    });
  }
  get_client_ibtana_elementor_templates();




  // Overlay Modal JS START

  $('.theme-browser.content-filterable').on('click', '.more-details', function() {

    var $card_parent = $( this ).closest('.theme');

    var template_name = $( $card_parent ).find( '.theme-name' ).text();
    var template_slug = $( $card_parent ).attr( 'data-slug' );
    var template_img  = $( $card_parent ).find( 'img' ).attr( 'src' );
    var template_desc = $( this ).attr( 'data-description' );
    var template_iframe = $( this ).attr( 'data-iframe' );

    var is_template_paid = $( this ).attr( 'data-is-paid' );

    $( '.wp-full-overlay-sidebar .theme-name' ).text( template_name );
    $( '.wp-full-overlay-sidebar .theme-screenshot' ).attr( 'src', template_img );
    $( '.wp-full-overlay-sidebar .theme-description' ).text( template_desc );
    $( '.theme-install-overlay .button.button-primary.theme-install' ).attr( 'data-slug', template_slug );
    $( '.theme-install-overlay .button.button-primary.theme-install' ).attr( 'data-is-paid', is_template_paid );
    $( '.theme-install-overlay .wp-full-overlay-main iframe' ).attr( 'src', template_iframe );


    $('.theme-install-overlay').show();
  });

  $('.close-full-overlay').on('click', function() {
    $('.theme-install-overlay .wp-full-overlay-main iframe').attr('src', '');
    $('.theme-install-overlay').hide();
  });

  $('.collapse-sidebar.button').on('click', function() {
    $('.theme-install-overlay').toggleClass('expanded').toggleClass('collapsed');
  });


  jQuery('form#sirat_demo_importer_license_form').on('submit', function(e) {


    jQuery('.theme_activation_spinner').show();
    e.preventDefault();
    var key_to_send = jQuery('form#sirat_demo_importer_license_form').serializeArray()[0].value;

    if (key_to_send == "") {
      alert('Please Enter the license key first!');
      return;
    } else {
      jQuery.post(
        sirat_wizard_script_params.ajaxurl,
        {
          action:                           'wz_activate_sirat_demo_importer',
          wpnonce:                          sirat_wizard_script_params.wpnonce,
          sirat_demo_importer_license_key:  key_to_send
        },
        function(data, status) {
          if (status == 'success') {
            if (data.status) {
              // location.reload(true);

              jQuery.notify(data.msg, {
                position: "right bottom",
                className: "success"
              });
              // document.querySelector('.tablinks[data-tab="demo_offer"]').click();
              sirat_wizard_script_params.license_key = key_to_send;

              jQuery('.theme_activation_spinner').hide();
              jQuery('form#sirat_demo_importer_license_form button[type="submit"]').css("background-color:#0a9d2c");
              jQuery('form#sirat_demo_importer_license_form button[type="submit"]').text('Activated');
              jQuery('form#sirat_demo_importer_license_form button[type="submit"]').attr('disabled', 'disabled');

              $('.theme_activation-wrapper .button.btn-close').click();
              $('.button.button-primary.theme-install').click();

            } else {
              jQuery.notify(data.msg, {
                position: "right bottom"
              });
              jQuery('.theme_activation_spinner').hide();
            }
          } else {
            jQuery('.theme_activation_spinner').hide();
          }
        },
        'json'
      );
    }
  });


  jQuery('#sirat_demo_importer_license_form button[id="change--key"]').on('click', function() {
    var $sirat_demo_importer_license_form = jQuery('#sirat_demo_importer_license_form');
    $sirat_demo_importer_license_form.find('input[name="sirat_demo_importer_license_key"]').val('');
    $sirat_demo_importer_license_form.find('input[name="sirat_demo_importer_license_key"]').attr('disabled', false);
    $sirat_demo_importer_license_form.find('button[type="submit"]').val('');
    $sirat_demo_importer_license_form.find('button[type="submit"]').attr('disabled', false);
    $sirat_demo_importer_license_form.find('button[type="submit"]').text('Activate');
    jQuery('#start-now-next').hide();
    jQuery(this).remove();
  });


  $( '.theme_activation-wrapper .button.btn-close' ).on('click', function() {
    $( '#theme_activation' ).hide();
  });


  $('.button.button-primary.theme-install').on('click', function() {

    var elementor_template_slug = $(this).attr('data-slug');
    var elementor_is_paid       = $(this).attr('data-is-paid');

    $( '#theme-install-overlay' ).addClass( 'sirat-demo-importer-spinning' );

    if ( elementor_is_paid == '1' ) {
      jQuery.post(sirat_wizard_script_params.ajaxurl, {
        action: 'sirat_demo_importer_get_the_key_status'
      }, function(data, success) {
        if ( data.status == "true" ) {
          sirat_demo_importer_step_popup.init( elementor_template_slug, elementor_is_paid );
        } else {
          $( '#theme-install-overlay' ).removeClass( 'sirat-demo-importer-spinning' );
          $( '#theme_activation' ).show();
        }
      });
    } else {
      sirat_demo_importer_step_popup.init( elementor_template_slug, elementor_is_paid );
    }

  });
  // Overlay Modal JS START


  // Step Popup JS START
  var sirat_demo_importer_step_popup = (function() {

    var ive_demo_step_container           = undefined;
    var step_pointer_current_step_element = undefined;
    var step_pointer_current_step         = undefined;

    var elementor_template_response = {};

    var selectors = {
      close_button:   '.sirat-demo-importer-step-close-button',
      popup_modal:    '.sirat-demo-importer-plugin-popup',

      step_btn_wrap:  '.sirat-demo-importer-demo-step-controls',
      next_button:    '.sirat-demo-importer-demo-btn.sirat-demo-importer-demo-main-btn'
    };

    var $close_button = $( selectors.close_button );
    var $popup_modal  = $( selectors.popup_modal );
    var $next_button  = $( selectors.next_button );

    var callbacks = {
      init: function() {
        $close_button = $( selectors.close_button );
        $popup_modal  = $( selectors.popup_modal );
        $next_button  = $( selectors.next_button );

        $close_button.on('click', function() {
          $popup_modal.remove();
        });

        $next_button.on('click', function(e) {
          e.preventDefault();

          $next_button.prop( 'disabled', true );
          $next_button.addClass( 'merlin__button--loading' );

          step_pointer_current_step_element = $('.sirat-demo-importer-demo-step.active');
          step_pointer_current_step = step_pointer_current_step_element.attr('data-step');

          if ( typeof callbacks[step_pointer_current_step] != 'undefined' ) {
            callbacks[step_pointer_current_step]();
            return false;
          } else {
            return true;
          }
        });
      },

      do_next_step: function() {
        do_next_step();
      },

      theme_install: function(btn) {
        var themes = new ThemeManager();
        themes.init(btn);
      },

      plugin_install: function(btn) {
        var plugins = new PluginManager();
        plugins.init(btn);
      },

      demo_import: function(btn) {
        var importDemo = new ImportManager();
        importDemo.init(btn);
      },

      demo_finish: function() {
        $close_button.trigger('click');
      }

    };

    function valBetween( v, min, max ) {
        return (Math.min(max, Math.max(min, v)));
    }

    function update_progress_bar( value = '' ) {

      console.log( 'value', value );

      if ( value == '' ) {
        value = parseFloat( $('.js-merlin-progress-bar-percentage').attr( 'data-per' ) );
        console.log( 'value', value );
        value = 0.1 + value;
        console.log( 'value', value );
      }

      $('.js-merlin-progress-bar').css( 'width', (value) * 100 + '%' );

      var $percentage = valBetween( ((value) * 100) , 0, 99);

      $('.js-merlin-progress-bar-percentage').attr( 'data-per', value );
      $('.js-merlin-progress-bar-percentage').html( Math.round( $percentage ) + '%' );

      if ( 1 == value || value > 0.99 ) {
        console.log( 'under if' );
        clearInterval( window.progress_bar_interval );
      }
    }

    function do_next_step() {
      if ( !step_pointer_current_step_element.next().length ) {

      } else {
        step_pointer_current_step_element.fadeOut(250, function() {

          step_pointer_current_step_element.removeClass( 'active' );
          step_pointer_current_step_element = step_pointer_current_step_element.next();
          step_pointer_current_step_element.fadeIn(250).addClass( 'active' );

          // Dots
          $('.sirat-demo-importer-steps-pills li').removeClass('active');
          $( '.sirat-demo-importer-steps-pills li:nth-child(' + ( 1 + step_pointer_current_step_element.index() ) + ')' ).addClass( 'active' );
        });
      }



      $next_button.prop( 'disabled', false );
      $next_button.removeClass( 'merlin__button--loading' );

    }


    function ImportManager() {

      function import_elementor() {



        jQuery.post(
          sirat_wizard_script_params.ajaxurl, {
            action: 'sirat_demo_importer_setup_elementor',
            wpnonce: sirat_wizard_script_params.wpnonce,
            elementor_template_response: elementor_template_response
          }, ajax_callback).fail(ajax_callback);
      }

      return {
        init: function(btn) {
          $('.merlin__button--loading__spinner').remove();
          $next_button.append(
            `<div class="merlin__progress-bar">
            	<span class="js-merlin-progress-bar" style="width: 0%;"></span>
            </div>
            <span class="js-merlin-progress-bar-percentage" data-per="0">0%</span>`
          );

          window.progress_bar_interval = setInterval( function() {
            update_progress_bar();
          }, 1000 );

          ajax_callback = function(response) {
            update_progress_bar(1);

            $('.merlin__progress-bar').remove();
            $('.js-merlin-progress-bar-percentage').remove();
            $('.sirat-demo-importer-demo-main-btn').removeClass('merlin__button--loading');

            jQuery( '[data-step="demo_finish"] .button.button-secondary' ).attr( 'href', response.edit_post_link );
            jQuery( '[data-step="demo_finish"] .button.button-primary' ).attr( 'href', response.permalink );
            do_next_step();
          }
          import_elementor();
        }
      }
    }


    function ThemeManager() {
      var complete;
      var items_completed = 0;
      var current_theme_item = '';
      var $current_node;
      var current_item_hash = '';

      function ajax_callback(response) {

        if (typeof response == 'object' && typeof response.message != 'undefined') {
          $current_node.find('span').text(response.message);
          if (typeof response.url != 'undefined') {
            // we have an ajax url action to perform.

            if (response.hash == current_item_hash) {
              $current_node.find('span').text("failed");
              find_next_theme();
            } else {
              current_item_hash = response.hash;
              jQuery.get(response.url, function(response2) {
                process_current_theme();
                $current_node.find('span').text(response.message + sirat_wizard_script_params.verify_text);
              }).fail(ajax_callback);
            }

          } else if (typeof response.done != 'undefined') {
            // finished processing this plugin, move onto next
            find_next_theme();
          } else {
            // error processing this plugin
            find_next_theme();
          }
        } else {
          // error - try again with next plugin
          $current_node.find('span').text("ajax error");
          find_next_theme();
        }
      }


      function process_current_theme() {
        if (current_theme_item) {
          // query our ajax handler to get the ajax to send to TGM
          // if we don't get a reply we can assume everything worked and continue onto the next one.
          jQuery.post(sirat_wizard_script_params.ajaxurl, {
            action: 'sirat_demo_importer_setup_themes',
            wpnonce: sirat_wizard_script_params.wpnonce,
            slug: current_theme_item
          }, ajax_callback).fail(ajax_callback);

        }
      }

      function find_next_theme() {
        var do_next = false;
        if ( $current_node ) {
          if (!$current_node.data('done_item')) {
            items_completed++;
            $current_node.data('done_item', 1);
          }
          $current_node.find('.spinner').css('visibility', 'hidden');
        }
        var $li = $('[data-step="theme_install"] .sirat-demo-importer-step-checkbox-container');
        $li.each(function() {
          if (current_theme_item == '' || do_next) {
            current_theme_item = $(this).attr('sirat-demo-importer-template-text-domain');
            $current_node = $(this);
            process_current_theme();
            do_next = false;
          } else if ($(this).attr('sirat-demo-importer-template-text-domain') == current_theme_item) {
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
          complete = function() {
            do_next_step();
          };
          find_next_theme();
        }
      }
    }


    function PluginManager() {

      var complete;
      var items_completed = 0;
      var current_plugin_text_domain = '';
      var current_plugin_file = '';
      var $current_node;
      var current_item_hash = '';

      function ajax_callback(response) {

        if (typeof response == 'object' && typeof response.message != 'undefined') {
          $current_node.find('span').text(response.message);

          // if (typeof response.url != 'undefined') {
          if ( typeof response.action != 'undefined' ) {
            // we have an ajax url action to perform.

            if (response.hash == current_item_hash) {
              $current_node.find('span').text("failed");
              find_next_plugin();
            } else {
              current_item_hash = response.hash;

              var plugin_obj_to_install = response.plugin;

              if ( response.action == "sirat-demo-importer-bulk-install" ) {

                // Check the source type

                if ( plugin_obj_to_install.source_type == "bundled" ) {

                  var request_obj = {
                    action:                 'sirat_demo_importer_install_and_activate_plugin',
                    plugin_obj_to_install:  plugin_obj_to_install
                  };

                  jQuery.post(
                    sirat_wizard_script_params.ajaxurl,
                    request_obj,
                    function(response2) {
                      process_current_plugin();
                      $current_node.find('span').text(response.message + sirat_wizard_script_params.verify_text);
                    }
                  ).fail(
                    ajax_callback
                  );

                } else if ( plugin_obj_to_install.source_type == "repo" ) {

                  wp.updates.installPlugin({
                    slug:     plugin_obj_to_install.slug,
                    success:  function(data) {
                      process_current_plugin();
                      $current_node.find('span').text( response.message + sirat_wizard_script_params.verify_text );
                    },
                    error: function(data) {
                      ajax_callback();
                    },
                  });

                }

              } else if ( response.action == "sirat-demo-importer-bulk-activate" ) {

                jQuery.get(plugin_obj_to_install.url, function(response2) {
                  process_current_plugin();
                  $current_node.find('span').text(response.message + sirat_wizard_script_params.verify_text);
                }).fail(ajax_callback);

              } else if ( response.action == "sirat-demo-importer-bulk-update" ) {

                jQuery.get(plugin_obj_to_install.url, function(response2) {
                  process_current_plugin();
                  $current_node.find('span').text(response.message + sirat_wizard_script_params.verify_text);
                }).fail(ajax_callback);

              } else {

                jQuery.post(response.url, response, function(response2) {
                  process_current_plugin();
                  $current_node.find('span').text(response.message + sirat_wizard_script_params.verify_text);
                }).fail(ajax_callback);

              }

            }

          } else if (typeof response.done != 'undefined') {
            // finished processing this plugin, move onto next
            find_next_plugin();
          } else {
            // error processing this plugin
            find_next_plugin();
          }

        } else {
          // error - try again with next plugin
          $current_node.find('span').text("ajax error");
          find_next_plugin();
        }
      }

      function process_current_plugin() {

        if (current_plugin_text_domain) {
          // query our ajax handler to get the ajax to send to TGM
          // if we don't get a reply we can assume everything worked and continue onto the next one.
          jQuery.post(sirat_wizard_script_params.ajaxurl, {
            action:             'sirat_demo_importer_setup_plugins_step_popup',
            wpnonce:            sirat_wizard_script_params.wpnonce,
            slug:               current_plugin_text_domain,
            file:               current_plugin_file,
            elementor_plugins:  elementor_template_response.elementor_template_plugins
          }, ajax_callback).fail(ajax_callback);
        }
      }

      function find_next_plugin() {
        var do_next = false;
        if ($current_node) {
          if (!$current_node.data('done_item')) {
            items_completed++;
            $current_node.data('done_item', 1);
          }
          $current_node.find('.spinner').css('visibility', 'hidden');
        }
        var $li = $('[data-step="plugin_install"] .sirat-demo-importer-step-checkbox-container');
        $li.each(function() {
          if (current_plugin_text_domain == '' || do_next) {
            current_plugin_text_domain = $(this).attr('sirat-plugin-text-domain');
            current_plugin_file = $(this).attr('sirat-demo-importer-plugin-main-file');
            $current_node = $(this);
            process_current_plugin();
            do_next = false;
          } else if ( $(this).attr('sirat-plugin-text-domain') == current_plugin_text_domain ) {
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
          // $('.envato-wizard-plugins').addClass('installing');
          complete = function() {
            do_next_step();
          };
          find_next_plugin();
        }
      }

    }


    return {
      init: function( elementor_template_slug, is_paid ) {

        var data_to_send = {
          slug:   elementor_template_slug
        };

        if ( is_paid == "1" ) {
          data_to_send.domain = sirat_wizard_script_params.site_url;
          data_to_send.key    = sirat_wizard_script_params.license_key;
        }

        $( '#theme-install-overlay' ).addClass( 'sirat-demo-importer-spinning' );

        jQuery.ajax({
          method: "POST",
          url: sirat_wizard_script_params.SIRAT_DEMO_IMPORTER_LICENCE_ENDPOINT + "get_client_ibtana_elementor_template_details",
          data: JSON.stringify( data_to_send ),
          dataType: 'json',
          contentType: 'application/json',
        }).done(function( template_data ) {

          if ( template_data.success === true ) {

            elementor_template_response = template_data.data;

            jQuery.post(sirat_wizard_script_params.ajaxurl, {
              action: 'sirat_demo_importer_step_popup',
              data: template_data.data
            }, function(data, success) {
              if ( success == "success" ) {
                $('#elementor_templates').append( data );
                callbacks['init']();
              }
            });

          }

          $('#theme-install-overlay').removeClass('sirat-demo-importer-spinning');

        });



      },
    }

  })();
  // Step Popup JS END


})(jQuery);
