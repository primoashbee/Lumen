$(document).ready(function(){
  var scroll = $(window).scrollTop();

  $('#sidebar-toggle').click(function(){
          $(this).toggleClass('active');
          $('#sidebar').toggleClass('sidebar-full');
          $('#content-full').toggleClass('pl-64');
          $('#regular').toggleClass('visible-on-sidebar-regular');
          $('#mini').toggleClass('visible-on-sidebar-mini');
          $('.navbar-brand.l-text').toggleClass('full-nav');
          $('.logo-container').toggleClass('full-nav');
          $('.logo-wrapper').toggleClass('pl-24');
  });

  $('.dark-badge').click(function(){
    $('body').removeClass('light-mode');
    $('body').addClass('dark-mode');
  });

  // $('#is_self_employed').click(function(){
  //   $('#is_employed').toggleClass('disabled').toggle().prop('disabled',function(){
  //     return ! $(this).prop('disabled');
  //   })
  // });

  // $('#is_employed').click(function(){
  //   $('#is_self_employed').toggleClass('disabled').toggle().prop('disabled',function(){
  //     return ! $(this).prop('disabled');
  //   })
  // });

  // $('#spouse_is_self_employed').click(function(){
  //   $('#spouse_is_employed').toggleClass('disabled').toggle().prop('disabled',function(){
  //     return ! $(this).prop('disabled');
  //   })
  // });

  // $('#spouse_is_employed').click(function(){
  //   $('#spouse_is_self_employed').toggleClass('disabled').toggle().prop('disabled',function(){
  //     return ! $(this).prop('disabled');
  //   })
  // });


  // $('#has_remittance').click(function(){
  //   $('#has_pension').toggleClass('disabled').toggle().prop('disabled',function(){
  //     return ! $(this).prop('disabled');
  //   })
  // });

  // $('#has_pension').click(function(){
  //   $('#has_remittance').toggleClass('disabled').toggle().prop('disabled',function(){
  //     return ! $(this).prop('disabled');
  //   })
  // });

  var qsRegex;

   var $settings = $('.settings').isotope({
      itemSelector: '.settings-item',
      layoutMode: 'fitRows',
      filter: function() {
        
        return qsRegex ? $(this).text().match( qsRegex ) : true;
      }
    });

  
  var $quicksearch = $('#search_menu').keyup( debounce( function() {
    qsRegex = new RegExp( $quicksearch.val(), 'gi' );
    $settings.isotope();
  }, 200 ) );
  function debounce( fn, threshold ) {
    var timeout;
    threshold = threshold || 100;
    return function debounced() {
      clearTimeout( timeout );
      var args = arguments;
      var _this = this;
      function delayed() {
        fn.apply( _this, args );
      }
      timeout = setTimeout( delayed, threshold );
    };
  }

   $('.btn-filters').on( 'click', function() {
      var filterValue = $(this).attr('data-filter');
      $settings.isotope({ filter: filterValue });
    });


  $('.white-badge').click(function(){
    $('body').removeClass('dark-mode');
    $('body').addClass('light-mode');
  });

  $('.cb-type').click(function(){
    $( 'div[data-attribute=' + $(this).attr('id') + ']').toggleClass('active');
  });

  $(window).scroll(function(){
    // console.log($('#lastname').val());
    var scroll = $(window).scrollTop();
    if (scroll - 10 >= 120) {
      $('.navbar').addClass('fixed-top');
    }else{
      $('.navbar').removeClass('fixed-top');
    }
  });

});