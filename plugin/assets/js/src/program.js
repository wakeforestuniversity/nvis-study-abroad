(function () {
	/* eslint-disable no-undef,valid-typeof */
	if (typeof Tabby !== undefined) {
		/* eslint-disable no-unused-vars */
		const tabs = new Tabby('[data-tabs]');

		if (tabs) {
			const tablist = document.querySelector('[role="tablist"]');

			tablist.addEventListener('click', nvisScrollToSelectedTab);
			tablist.parentElement.classList += ' nvis-tabs-container';

			nvisScrollToSelectedTab();
		}
	}
})();

function nvisScrollToSelectedTab() {
	const tablist = document.querySelector('[role="tablist"]');
	const selectedTab = document.querySelector(
		'[role="tab"][aria-selected="true"]'
	);

	if (!tablist || !selectedTab) {
		return;
	}

	const tablistStyles = getComputedStyle(tablist);

	if (tablistStyles.overflow !== 'auto') {
		return;
	}

	const containerWidth = tablist.offsetWidth;
	const selectedTabRect = selectedTab.getBoundingClientRect();

	const scrollAmount =
		selectedTabRect.left - containerWidth / 2 + selectedTab.offsetWidth / 2;
	tablist.scrollLeft += scrollAmount;
}
