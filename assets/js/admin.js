jQuery(window).on('load', function () {
	var $ = jQuery;
	//taxonomy
	jQuery('#publish, .editor-post-publish-panel__toggle, .editor-post-publish-button').click(function (e) {
		var taxonomies = vgrt_data.required_taxonomies;

		var allowed = true;
		jQuery.each(taxonomies, function (value, taxonomy) {
			var label = taxonomy.label;
			var tx = value;
			var rest_base = taxonomy.rest_base || tx;

			// If woocommerce global attribute
			if (tx.indexOf('pa_') > -1) {
				var $scope = $('.wc-metabox.' + tx).first();
				if (!$scope.length) {
					alert(vgrt_data.error_message.replace('{taxonomy_name}', label));
					allowed = false;
					return false;
				}
			} else {
				// If the taxonomy metabox is not visible, we don't do any validation
				var $scope = $('#' + tx + 'div, .components-panel__body-toggle:contains(' + label + '), #tagsdiv-' + tx + ', .wc-metabox.' + tx).first();
				if (!$scope.length) {
					return true;
				}
			}

			// If woocommerce global attribute
			if (tx.indexOf('pa_') > -1) {
				var hasValues = $scope.find('.attribute_values').val();
			} else {
				// Is gutenberg?
				if (jQuery('.components-panel__body-toggle').length) {
					var postData = jQuery.extend({}, wp.data.select("core/editor").getCurrentPost(), wp.data.select("core/editor").getPostEdits());
					var hasValues = typeof postData[rest_base] !== 'undefined' ? postData[rest_base].length : false;
				} else {
					var hasValues = $scope.find('input:checked').length > 0 || $scope.find('textarea').val();
				}
			}
			if (!hasValues) {
				alert(vgrt_data.error_message.replace('{taxonomy_name}', label));
				allowed = false;
			}
		});
		return allowed;
	});
});