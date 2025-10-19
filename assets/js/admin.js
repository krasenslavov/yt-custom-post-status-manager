/**
 * YT Custom Post Status Manager - Admin Script
 *
 * @format
 * @package YT_Custom_Post_Status_Manager
 */

(function ($) {
	"use strict";

	$(document).ready(function () {
		/**
		 * Apply color coding to post rows
		 */
		function applyStatusColors() {
			if (typeof ytCpsmData === "undefined" || !ytCpsmData.statuses) {
				return;
			}

			$("#the-list tr").each(function () {
				const $row = $(this);
				const $statusSpan = $row.find(".post-state");

				// Check each custom status
				$.each(ytCpsmData.statuses, function (slug, data) {
					if ($statusSpan.text().includes(data.label)) {
						$row
							.css({
								"box-shadow": `inset 4px 0 0 0 ${data.color}`
							})
							.attr("data-status-color", data.color);
					}
				});

				// Also check the status column
				const statusText = $row.find(".column-status").text().trim();
				$.each(ytCpsmData.statuses, function (slug, data) {
					if (statusText === data.label) {
						$row
							.css({
								"box-shadow": `inset 4px 0 0 0 ${data.color}`
							})
							.attr("data-status-color", data.color);
					}
				});
			});
		}

		/**
		 * Add custom statuses to quick edit
		 */
		function addQuickEditStatuses() {
			if (typeof ytCpsmData === "undefined" || !ytCpsmData.statuses) {
				return;
			}

			const $statusSelect = $('.inline-edit-status select[name="_status"]');

			if ($statusSelect.length) {
				$.each(ytCpsmData.statuses, function (slug, data) {
					// Check if option doesn't already exist
					if ($statusSelect.find('option[value="' + slug + '"]').length === 0) {
						$statusSelect.append($("<option></option>").attr("value", slug).text(data.label));
					}
				});
			}
		}

		/**
		 * Update quick edit status when clicking edit
		 */
		$(document).on("click", ".editinline", function () {
			const $row = $(this).closest("tr");
			const postId = $row.attr("id").replace("post-", "");

			// Get the current status
			setTimeout(function () {
				const $inline = $("#inline_" + postId);
				const currentStatus = $inline.find("._status").text();

				// Set the status in quick edit
				$('.inline-edit-status select[name="_status"]').val(currentStatus);
			}, 100);
		});

		/**
		 * Add custom statuses to bulk edit
		 */
		function addBulkEditStatuses() {
			if (typeof ytCpsmData === "undefined" || !ytCpsmData.statuses) {
				return;
			}

			const $bulkStatusSelect = $('.bulk-edit-status select[name="_status"]');

			if ($bulkStatusSelect.length) {
				$.each(ytCpsmData.statuses, function (slug, data) {
					if ($bulkStatusSelect.find('option[value="' + slug + '"]').length === 0) {
						$bulkStatusSelect.append($("<option></option>").attr("value", slug).text(data.label));
					}
				});
			}
		}

		// Initialize
		applyStatusColors();
		addQuickEditStatuses();
		addBulkEditStatuses();

		// Re-apply colors after AJAX operations
		$(document).ajaxComplete(function () {
			applyStatusColors();
		});

		// Update colors when quick edit is opened
		$(document).on("click", ".editinline", function () {
			setTimeout(function () {
				addQuickEditStatuses();
			}, 50);
		});

		// Update colors when bulk edit is opened
		$(document).on("click", "#doaction, #doaction2", function () {
			setTimeout(function () {
				addBulkEditStatuses();
			}, 50);
		});
	});
})(jQuery);
