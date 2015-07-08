(function ( $ ) {
	"use strict";

	$(function () {

		$('#myUsers').change(function(){
			if ( $(this).val() != -1 ) {
				// Reload the page with id
				top.location = '?page=rs-hide-admin&id='+$(this).val();
			} else {
				top.location = '?page=rs-hide-admin';
			}
		});

		$('.selectAll').click(function(){
			$('input[type="checkbox"]').prop('checked', true);
		});

		$('.deselectAll').click(function(){
			$('input[type="checkbox"]').prop('checked', false);
		});

		$('.subCheck').change(function(){
			if ( $(this).prop('checked') ) {
				// We need to make the parent checkbox 'checked'
				$('[data-menu="'+$(this).attr('data-sub-menu')+'"]').prop('checked', true);
			}
			// We now need to check to see if all the child of the parent have been unchecked to uncheck the parent
			var myCount = 0;
			$('[data-sub-menu="'+$(this).attr('data-sub-menu')+'"]:checked').each(function(){
				myCount++;
			});
			// We now need to check to see if our count is = 0
			if ( myCount == 0 ) {
				$('[data-menu="'+$(this).attr('data-sub-menu')+'"]').prop('checked', false);
			}
			
		});

		$('.mainMenu').click(function(){
			if ( $(this).prop('checked') ) {
				$('*[data-sub-menu="'+$(this).attr('data-menu')+'"]').prop('checked', true);
			} else {
				$('*[data-sub-menu="'+$(this).attr('data-menu')+'"]').prop('checked', false);
			}
			
		});

		$('body').on('click', '.fa-plus-square-o', function(){
			var myLi = $(this).parent();
			// We should remove the other icon
			// Now let's show the children
			$('ul', myLi).show();
			$('.fa-plus-square-o', myLi).removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
		});

		$('body').on('click', '.fa-minus-square-o', function(){
			var myLi = $(this).parent();
			// We should remove the other icon
			// Now let's show the children
			$('ul', myLi).hide();
			$('.fa-minus-square-o', myLi).removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
		});

	});

}(jQuery));