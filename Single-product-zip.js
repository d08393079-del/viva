document.addEventListener('DOMContentLoaded', () => {
    const block = document.querySelector('.zip-related-area');
	const startingPrice = document.querySelector('#starting_at')
    if (!block) return;

    let postcode = '';

	const cookieMatch = document.cookie.match(/(?:^|; )guest_zip_code=([^;]*)/);

	if (cookieMatch) {
		postcode = decodeURIComponent(cookieMatch[1]).trim();
	}

    if (postcode) {
        block.style.display = 'flex';
		startingPrice.style.display = 'none'
    }
});
