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

  $('#is_self_employed').click(function(){
    $('#is_employed').toggleClass('disabled').toggle().prop('disabled',function(){
      return ! $(this).prop('disabled');
    })
  });

  $('#is_employed').click(function(){
    $('#is_self_employed').toggleClass('disabled').toggle().prop('disabled',function(){
      return ! $(this).prop('disabled');
    })
  });

  $('#spouse_is_self_employed').click(function(){
    $('#spouse_is_employed').toggleClass('disabled').toggle().prop('disabled',function(){
      return ! $(this).prop('disabled');
    })
  });

  $('#spouse_is_employed').click(function(){
    $('#spouse_is_self_employed').toggleClass('disabled').toggle().prop('disabled',function(){
      return ! $(this).prop('disabled');
    })
  });

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
    if (scroll >= 80) {
      $('.navbar').addClass('fixed-top');
    }else{
      $('.navbar').removeClass('fixed-top');
    }
  });

});