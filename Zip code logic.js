jQuery(document).ready(function ($) {
    // Open modal when "Get quote" clicked
    $(document).on('click', '.get-quote-button, .get-quote-link', function (e) {
        e.preventDefault();
        $('#zipModal').fadeIn();
    });

    // Close modal
    $('.zip-close, .zip-later').on('click', function () {
        $('#zipModal').fadeOut();
    });

	// Submit zip code
	$('.zip-submit').on('click', function () {
		const userZipCode = $('#zipInput').val().trim();
		if (userZipCode) {
			$('.zip-code-placeholder').html('<div class="loading-spinner"></div>');
			$('#zipModal').fadeOut();

			$.ajax({
				url: zipCodeLogic.ajax_url,
				type: 'POST',
				data: {
					action: 'update_zip_code',
					nonce: zipCodeLogic.nonce,
					zip_code: userZipCode,
					url: zipCodeLogic.current_url
				},
				success: function (response) {
					if (response.success) {
						const reloadUrl = window.location.href + (window.location.search ? '&' : '?');
						window.location.href = reloadUrl;
					} else {
						alert(response.data.message || 'An error occurred. Please try again.');
						$('.zip-code-placeholder').html('<button class="button get-quote-button">Get quote</button><div class="custom-product-price get-quote-link">Enter zip code to see price</div>');
					}
				},
				error: function () {
					alert('An error occurred. Please try again.');
					$('.zip-code-placeholder').html('<button class="button get-quote-button">Get quote</button><div class="custom-product-price get-quote-link">Enter zip code to see price</div>');
				}
			});
		}
	});
});
